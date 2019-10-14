<?php

namespace Cthulhu\ir\names;

use Cthulhu\ir;
use Cthulhu\ir\nodes;

/**
 * Scopes contain mappings from names (typeof string) -> Symbol
 * The names table contains a mapping of node ids (typeof int) -> Symbol
 */

class Resolve {
  private $spans;
  private $name_to_symbol;
  private $symbol_to_name;
  private $root_scope;
  private $namespaces    = [];
  private $module_scopes = [];
  private $func_scopes   = [];
  private $block_scopes  = [];
  private $ref_symbols   = [];

  private function __construct(ir\Table $spans) {
    $this->spans = $spans;
    $this->name_to_symbol = new ir\Table();
    $this->symbol_to_name = new ir\Table();
  }

  private function make_ref_symbol(nodes\Name $node, ?RefSymbol $parent): RefSymbol {
    $symbol = new RefSymbol($parent);
    $this->name_to_symbol->set($node, $symbol);
    $this->symbol_to_name->set($symbol, $node);
    return $symbol;
  }

  private function make_var_symbol(nodes\Name $node): VarSymbol {
    $symbol = new VarSymbol();
    $this->name_to_symbol->set($node, $symbol);
    $this->symbol_to_name->set($symbol, $node);
    return $symbol;
  }

  private function set_symbol(nodes\Name $node, Symbol $symbol): void {
    $this->name_to_symbol->set($node, $symbol);
  }

  private function current_module_scope(): Scope {
    return end($this->module_scopes);
  }

  private function push_module_scope(Scope $scope): void {
    array_push($this->module_scopes, $scope);
  }

  private function current_func_scope(): Scope {
    return end($this->func_scopes);
  }

  private function pop_module_scope(): Scope {
    return array_pop($this->module_scopes);
  }

  private function push_func_scope(Scope $scope): void {
    array_push($this->func_scopes, $scope);
  }

  private function pop_func_scope(): Scope {
    return array_pop($this->func_scopes);
  }

  private function has_block_scope(): bool {
    return !empty($this->block_scopes);
  }

  private function current_block_scope(): Scope {
    return end($this->block_scopes);
  }

  private function push_block_scope(Scope $scope): void {
    array_push($this->block_scopes, $scope);
  }

  private function pop_block_scope(): Scope {
    return array_pop($this->block_scopes);
  }

  private function current_ref_symbol(): RefSymbol {
    return end($this->ref_symbols);
  }

  private function push_ref_symbol(RefSymbol $symbol): void {
    array_push($this->ref_symbols, $symbol);
  }

  private function pop_ref_symbol(): RefSymbol {
    return array_pop($this->ref_symbols);
  }

  private function root_scope(): Scope {
    return $this->root_scope;
  }

  private function add_namespace(Symbol $symbol, Scope $namespace): void {
    $this->namespaces[$symbol->get_id()] = $namespace;
  }

  private function get_namespace(Symbol $symbol): ?Scope {
    return $this->namespaces[$symbol->get_id()];
  }

  public static function names(ir\Table $spans, nodes\Program $prog): array {
    $ctx = new self($spans);

    ir\Visitor::walk($prog, [
      'enter(Program)' => function (nodes\Program $prog) use ($ctx) {
        self::enter_program($ctx, $prog);
      },
      'exit(Program)' => function (nodes\Program $prog) use ($ctx) {
        self::exit_program($ctx, $prog);
      },
      'enter(Library)' => function (nodes\Library $library) use ($ctx) {
        self::enter_library($ctx, $library);
      },
      'exit(Library)' => function (nodes\Library $library) use ($ctx) {
        self::exit_library($ctx, $library);
      },
      'enter(ModItem)' => function (nodes\ModItem $item) use ($ctx) {
        self::enter_mod_item($ctx, $item);
      },
      'exit(ModItem)' => function (nodes\ModItem $item) use ($ctx) {
        self::exit_mod_item($ctx, $item);
      },
      'UseItem' => function (nodes\UseItem $item) use ($ctx) {
        self::use_item($ctx, $item);
      },
      'enter(FuncItem)' => function (nodes\FuncItem $item) use ($ctx) {
        self::enter_func_item($ctx, $item);
      },
      'FuncParam' => function (nodes\FuncParam $param) use ($ctx) {
        self::func_param($ctx, $param);
      },
      'exit(FuncItem)' => function (nodes\FuncItem $item) use ($ctx) {
        self::exit_func_item($ctx, $item);
      },
      'NativeFuncItem' => function (nodes\NativeFuncItem $item) use ($ctx) {
        self::native_func_item($ctx, $item);
      },
      'NativeTypeItem' => function (nodes\NativeTypeItem $item) use ($ctx) {
        self::native_type_item($ctx, $item);
      },
      'LetStmt' => function (nodes\LetStmt $stmt) use ($ctx) {
        self::let_stmt($ctx, $stmt);
      },
      'enter(Block)' => function (nodes\Block $block) use ($ctx) {
        self::enter_block($ctx, $block);
      },
      'exit(Block)' => function (nodes\Block $block) use ($ctx) {
        self::exit_block($ctx, $block);
      },
      'Ref' => function (nodes\Ref $ref) use ($ctx) {
        self::ref($ctx, $ref);
      },
    ]);

    return [
      $ctx->name_to_symbol,
      $ctx->symbol_to_name,
    ];
  }

  public static function validate(ir\Table $names, nodes\Program $prog): void {
    ir\Visitor::walk($prog, [
      'Name' => function (nodes\Name $name) use ($names) {
        if ($names->has($name) === false) {
          throw new \Exception('missing symbol binding for a name');
        }
      }
    ]);
  }

  private static function enter_program(self $ctx): void {
    $ctx->root_scope = new Scope();
  }

  private static function exit_program(self $ctx): void {
    $ctx->root_scope = null;
  }

  private static function enter_library(self $ctx, nodes\Library $lib): void {
    $lib_name   = $lib->name->value;
    $lib_symbol = $ctx->make_ref_symbol($lib->name, null);
    $ctx->root_scope()->add_binding($lib_name, $lib_symbol);
    $ctx->push_ref_symbol($lib_symbol);

    $lib_scope = new Scope();
    $ctx->add_namespace($lib_symbol, $lib_scope);
    $ctx->push_module_scope($lib_scope);
  }

  private static function exit_library(self $ctx): void {
    $ctx->pop_ref_symbol();
    $ctx->pop_module_scope();
  }

  private static function enter_mod_item(self $ctx, nodes\ModItem $item): void {
    $mod_name   = $item->name->value;
    $mod_symbol = $ctx->make_ref_symbol($item->name, $ctx->current_ref_symbol());
    $ctx->current_module_scope()->add_binding($mod_name, $mod_symbol);
    $ctx->push_ref_symbol($mod_symbol);

    $mod_scope = new Scope();
    $ctx->add_namespace($mod_symbol, $mod_scope);
    $ctx->push_module_scope($mod_scope);
  }

  private static function exit_mod_item(self $ctx): void {
    $ctx->pop_ref_symbol();
    $ctx->pop_module_scope();
  }

  private static function use_item(self $ctx, nodes\UseItem $item): void {
    $namespace = $item->ref->extern
      ? $ctx->root_scope()
      : $ctx->current_module_scope();

    foreach ($item->ref->body as $segment) {
      $body_name = $segment->value;
      if ($body_symbol = $namespace->get_name($body_name)) {
        $ctx->set_symbol($segment, $body_symbol);
        if ($next_namespace = $ctx->get_namespace($body_symbol)) {
          $namespace = $next_namespace;
          continue;
        }
      }

      throw Errors::unknown_namespace_field($ctx->spans->get($segment), $segment);
    }

    if ($item->ref->tail instanceof nodes\StarRef) {
      foreach ($namespace->table as $name => $symbol) {
        $ctx->current_module_scope()->add_binding($name, $symbol);
      }
    } else if ($item->ref->tail instanceof nodes\Name) {
      $tail_name = $item->ref->tail->value;
      if ($tail_symbol = $namespace->get_name($tail_name)) {
        $ctx->set_symbol($item->ref->tail, $tail_symbol);
        $ctx->current_module_scope()->add_binding($tail_name, $tail_symbol);
      } else {
        throw Errors::unknown_namespace_field($ctx->spans->get($item->ref->tail), $item->ref->tail);
      }
    } else {
      throw new \Exception('unknown reference tail segment');
    }
  }

  private static function enter_func_item(self $ctx, nodes\FuncItem $item): void {
    $func_name   = $item->name->value;
    $func_symbol = $ctx->make_ref_symbol($item->name, $ctx->current_ref_symbol());
    $ctx->current_module_scope()->add_binding($func_name, $func_symbol);

    $func_scope = new Scope();
    $ctx->push_func_scope($func_scope);
  }

  private static function func_param(self $ctx, nodes\FuncParam $param): void {
    $param_name = $param->name->value;
    $param_symbol = $ctx->make_var_symbol($param->name);
    $ctx->current_func_scope()->add_binding($param_name, $param_symbol);
  }

  private static function exit_func_item(self $ctx): void {
    $ctx->pop_func_scope();
  }

  private static function native_func_item(self $ctx, nodes\NativeFuncItem $item): void {
    $func_name   = $item->name->value;
    $func_symbol = $ctx->make_ref_symbol($item->name, $ctx->current_ref_symbol());
    $ctx->current_module_scope()->add_binding($func_name, $func_symbol);
  }

  private static function native_type_item(self $ctx, nodes\NativeTypeItem $item): void {
    $type_name   = $item->name->value;
    $type_symbol = $ctx->make_ref_symbol($item->name, $ctx->current_ref_symbol());
    $ctx->current_module_scope()->add_binding($type_name, $type_symbol);
  }

  private static function let_stmt(self $ctx, nodes\LetStmt $stmt): void {
    $let_name   = $stmt->name->value;
    $let_symbol = $ctx->make_var_symbol($stmt->name);
    $ctx->current_module_scope()->add_binding($let_name, $let_symbol);
  }

  private static function enter_block(self $ctx): void {
    $block_scope = new Scope();
    $ctx->push_block_scope($block_scope);
  }

  private static function exit_block(self $ctx): void {
    $ctx->pop_block_scope();
  }

  private static function ref(self $ctx, nodes\Ref $ref): void {
    $is_extern = $ref->extern;
    $head_segments = $ref->head_segments;
    $tail_segment = $ref->tail_segment;

    // True iff the reference only has one segment and is not external.
    $is_nearby = $is_extern === false && empty($head_segments);

    if ($is_nearby) {
      $tail_name = $tail_segment->value;
      if ($ctx->has_block_scope()) {
        // If the reference exists inside of 1 or more block scopes, explore all
        // available block scopes to see if one of them contains the name. If
        // none of the block scopes have the name, check the most recent func
        // scope incase the name was a function parameter.
        $scopes = array_merge(
          array_reverse($ctx->block_scopes),
          [ $ctx->current_func_scope()
        ]);
        foreach ($scopes as $scope) {
          if ($tail_symbol = $scope->get_name($tail_name)) {
            $ctx->set_symbol($tail_segment, $tail_symbol);
            return;
          }
        }
      }

      if ($tail_symbol = $ctx->current_module_scope()->get_name($tail_name)) {
        // If the reference exists outside of a block scope or if none of the
        // current blocks scopes contain the name, try looking in the closest
        // module scope.
        $ctx->set_symbol($tail_segment, $tail_symbol);
        return;
      }

      throw Errors::unknown_name($ctx->spans->get($tail_segment), $tail_segment);
    } else {
      $scope = $is_extern
        ? $ctx->root_scope()
        : $ctx->current_module_scope();

      foreach ($head_segments as $head_segment) {
        $head_name = $head_segment->value;
        if ($head_symbol = $scope->get_name($head_name)) {
          $ctx->set_symbol($head_segment, $head_symbol);
          if ($next_scope = $ctx->get_namespace($head_symbol)) {
            $scope = $next_scope;
            continue;
          }
        }

        throw Errors::unknown_namespace_field($ctx->spans->get($head_segment), $head_segment);
      }

      $tail_name = $tail_segment->value;
      if ($tail_symbol = $scope->get_name($tail_name)) {
        $ctx->set_symbol($tail_segment, $tail_symbol);
      } else {
        throw Errors::unknown_namespace_field($ctx->spans->get($tail_segment), $tail_segment);
      }
    }
  }
}
