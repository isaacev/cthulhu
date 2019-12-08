<?php

namespace Cthulhu\ir\names;

use Cthulhu\Errors\Error;
use Cthulhu\ir;
use Cthulhu\ir\nodes;

/**
 * Scopes contain mappings from names (typeof string) -> Symbol
 * The names table contains a mapping of node ids (typeof int) -> Symbol
 */
class Resolve {
  private ?Scope $root_scope;
  private array $namespaces = [];
  private array $module_scopes = [];
  private array $func_scopes = [];
  private array $param_scopes = [];
  private array $block_scopes = [];
  private array $ref_symbols = [];

  private function make_ref_symbol(nodes\Name $node, ?RefSymbol $parent): RefSymbol {
    $symbol = new RefSymbol($parent);
    $node->set('symbol', $symbol);
    $symbol->set('node', $node);
    return $symbol;
  }

  private function make_var_symbol(nodes\Name $node): VarSymbol {
    $symbol = new VarSymbol();
    $node->set('symbol', $symbol);
    $symbol->set('node', $node);
    return $symbol;
  }

  private function make_type_symbol(nodes\Name $node): TypeSymbol {
    $symbol = new TypeSymbol();
    $node->set('symbol', $symbol);
    $symbol->set('node', $node);
    return $symbol;
  }

  private function set_symbol(nodes\Name $node, Symbol $symbol): void {
    $node->set('symbol', $symbol);
  }

  private function current_module_scope(): Scope {
    return end($this->module_scopes);
  }

  private function push_module_scope(Scope $scope): void {
    array_push($this->module_scopes, $scope);
  }

  private function has_func_scope(): bool {
    return !empty($this->func_scopes);
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

  private function has_param_scope(): bool {
    return !empty($this->param_scopes);
  }

  private function current_param_scope(): Scope {
    return end($this->param_scopes);
  }

  private function push_param_scope(Scope $scope): void {
    array_push($this->param_scopes, $scope);
  }

  private function pop_param_scope(): Scope {
    return array_pop($this->param_scopes);
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
    if (array_key_exists($symbol->get_id(), $this->namespaces)) {
      return $this->namespaces[$symbol->get_id()];
    } else {
      return null;
    }
  }

  /**
   * @param nodes\Program $prog
   * @throws Error
   * @noinspection PhpDocRedundantThrowsInspection
   */
  public static function names(nodes\Program $prog): void {
    $ctx = new self();

    ir\Visitor::walk($prog, [
      'enter(Program)' => function () use ($ctx) {
        self::enter_program($ctx);
      },
      'exit(Program)' => function () use ($ctx) {
        self::exit_program($ctx);
      },
      'enter(Library)' => function (nodes\Library $library) use ($ctx) {
        self::enter_library($ctx, $library);
      },
      'exit(Library)' => function () use ($ctx) {
        self::exit_library($ctx);
      },
      'enter(ModItem)' => function (nodes\ModItem $item) use ($ctx) {
        self::enter_mod_item($ctx, $item);
      },
      'exit(ModItem)' => function () use ($ctx) {
        self::exit_mod_item($ctx);
      },
      'UseItem' => function (nodes\UseItem $item) use ($ctx) {
        self::use_item($ctx, $item);
      },
      'enter(FuncHead)' => function (nodes\FuncHead $head) use ($ctx) {
        self::enter_func_head($ctx, $head);
      },
      'FuncParam' => function (nodes\FuncParam $param) use ($ctx) {
        self::func_param($ctx, $param);
      },
      'exit(FuncItem)' => function () use ($ctx) {
        self::exit_func_item($ctx);
      },
      'enter(NativeFuncItem)' => function (nodes\NativeFuncItem $item) use ($ctx) {
        self::enter_native_func_item($ctx, $item);
      },
      'exit(NativeFuncItem)' => function () use ($ctx) {
        self::exit_native_func_item($ctx);
      },
      'NativeTypeItem' => function (nodes\NativeTypeItem $item) use ($ctx) {
        self::native_type_item($ctx, $item);
      },
      'enter(UnionItem)' => function (nodes\UnionItem $item) use ($ctx) {
        self::enter_union_item($ctx, $item);
      },
      'exit(UnionItem)' => function (nodes\UnionItem $item) use ($ctx) {
        self::exit_union_item($ctx, $item);
      },
      'LetStmt' => function (nodes\LetStmt $stmt) use ($ctx) {
        self::let_stmt($ctx, $stmt);
      },
      'enter(Block)' => function () use ($ctx) {
        self::enter_block($ctx);
      },
      'exit(Block)' => function () use ($ctx) {
        self::exit_block($ctx);
      },
      'enter(MatchArm)' => function () use ($ctx) {
        self::enter_match_arm($ctx);
      },
      'exit(MatchArm)' => function () use ($ctx) {
        self::exit_match_arm($ctx);
      },
      'exit(VariantPattern)' => function (nodes\VariantPattern $pattern) use ($ctx) {
        self::exit_variant_pattern($ctx, $pattern);
      },
      'VariablePattern' => function (nodes\VariablePattern $pattern) use ($ctx) {
        self::variable_pattern($ctx, $pattern);
      },
      'NamedVariantConstructorFields' => function (nodes\NamedVariantConstructorFields $fields, ir\Path $path) use ($ctx) {
        self::named_variant_constructor_fields($ctx, $fields, $path);
      },
      'ParamNote' => function (nodes\ParamNote $note) use ($ctx) {
        self::param_note($ctx, $note);
      },
      'Ref' => function (nodes\Ref $ref) use ($ctx) {
        self::ref($ctx, $ref);
      },
    ]);
  }

  public static function validate(nodes\Program $prog): void {
    ir\Visitor::walk($prog, [
      'Name' => function (nodes\Name $name) {
        if ($name->has('symbol') === false) {
          die("missing symbol binding for a name '$name->value' at " . $name->get('span')->from);
        }
      },
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

    // Automatically add `use ::Kernel::Types::*;` to the top of all libraries.
    if ($lib->name->value !== 'Kernel') {
      self::link_kernel_types($ctx);
    }
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

    if ($item->get_attr('no_linked_kernel_types', false) === false) {
      self::link_kernel_types($ctx);
    }
  }

  private static function exit_mod_item(self $ctx): void {
    $ctx->pop_ref_symbol();
    $ctx->pop_module_scope();
  }

  /**
   * @param Resolve       $ctx
   * @param nodes\UseItem $item
   * @throws Error
   */
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

      throw Errors::unknown_namespace_field($segment->get('span'), $segment);
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
        throw Errors::unknown_namespace_field($item->ref->tail->get('span'), $item->ref->tail);
      }
    } else {
      die('unknown reference tail segment');
    }
  }

  private static function link_kernel_types(self $ctx): void {
    // Simulates `use ::Kernel::Types::*;` at the top of a library or module.
    $extern_namespace = $ctx->root_scope();
    $kernel_namespace = $ctx->get_namespace($extern_namespace->get_name('Kernel'));
    $types_namespace  = $ctx->get_namespace($kernel_namespace->get_name('Types'));
    foreach ($types_namespace->table as $name => $symbol) {
      $ctx->current_module_scope()->add_binding($name, $symbol);
    }
  }

  private static function enter_func_head(self $ctx, nodes\FuncHead $head): void {
    $func_name   = $head->name->value;
    $func_symbol = $ctx->make_ref_symbol($head->name, $ctx->current_ref_symbol());
    $ctx->current_module_scope()->add_binding($func_name, $func_symbol);

    $func_scope = new Scope();
    $ctx->push_func_scope($func_scope);

    $param_scope = new Scope();
    $ctx->push_param_scope($param_scope);
    foreach ($head->params as $param) {
      ir\Visitor::walk($param->note, [
        'ParamNote' => function (nodes\ParamNote $note) use ($ctx, $param_scope) {
          if ($param_scope->has_name($note->name->value) === false) {
            $type_symbol = $ctx->make_type_symbol($note->name);
            $param_scope->add_binding($note->name->value, $type_symbol);
          }
        },
      ]);
    }
  }

  private static function func_param(self $ctx, nodes\FuncParam $param): void {
    $param_name   = $param->name->value;
    $param_symbol = $ctx->make_var_symbol($param->name);
    $ctx->current_func_scope()->add_binding($param_name, $param_symbol);
  }

  private static function exit_func_item(self $ctx): void {
    $ctx->pop_param_scope();
    $ctx->pop_func_scope();
  }

  private static function enter_native_func_item(self $ctx, nodes\NativeFuncItem $item): void {
    $func_name   = $item->name->value;
    $func_symbol = $ctx->make_ref_symbol($item->name, $ctx->current_ref_symbol());
    $ctx->current_module_scope()->add_binding($func_name, $func_symbol);

    $func_scope = new Scope();
    $ctx->push_func_scope($func_scope);

    $param_scope = new Scope();
    $ctx->push_param_scope($param_scope);
    foreach ($item->note->inputs as $input_note) {
      ir\Visitor::walk($input_note, [
        'ParamNote' => function (nodes\ParamNote $note) use ($ctx, $param_scope) {
          if ($param_scope->has_name($note->name->value) === false) {
            $type_symbol = $ctx->make_type_symbol($note->name);
            $param_scope->add_binding($note->name->value, $type_symbol);
          }
        },
      ]);
    }
  }

  private static function exit_native_func_item(self $ctx): void {
    $ctx->pop_param_scope();
    $ctx->pop_func_scope();
  }

  private static function native_type_item(self $ctx, nodes\NativeTypeItem $item): void {
    $type_name   = $item->name->value;
    $type_symbol = $ctx->make_ref_symbol($item->name, $ctx->current_ref_symbol());
    $ctx->current_module_scope()->add_binding($type_name, $type_symbol);
  }

  /**
   * @param Resolve         $ctx
   * @param nodes\UnionItem $item
   * @throws Error
   */
  private static function enter_union_item(self $ctx, nodes\UnionItem $item): void {
    $param_scope = new Scope();
    $ctx->push_param_scope($param_scope);
    foreach ($item->params as $param) {
      if ($param_scope->has_name($param->name->value)) {
        throw Errors::duplicate_union_type_parameter($param->name->get('span'), $param->name);
      } else {
        $type_symbol = $ctx->make_type_symbol($param->name);
        $param_scope->add_binding($param->name->value, $type_symbol);
      }
    }
  }

  private static function exit_union_item(self $ctx, nodes\UnionItem $item): void {
    $ctx->pop_param_scope();

    $union_name   = $item->name->value;
    $union_symbol = $ctx->make_ref_symbol($item->name, $ctx->current_ref_symbol());
    $ctx->current_module_scope()->add_binding($union_name, $union_symbol);

    $union_scope = new Scope();
    $ctx->add_namespace($union_symbol, $union_scope);
    foreach ($item->variants as $variant) {
      $variant_name   = $variant->name->value;
      $variant_symbol = $ctx->make_ref_symbol($variant->name, $union_symbol);
      $union_scope->add_binding($variant_name, $variant_symbol);

      if ($variant instanceof nodes\NamedVariantDeclNode) {
        $variant_scope = new Scope();
        $ctx->add_namespace($variant_symbol, $variant_scope);
        foreach ($variant->fields as $field) {
          $field_name   = $field->name->value;
          $field_symbol = $ctx->make_var_symbol($field->name);
          $variant_scope->add_binding($field_name, $field_symbol);
        }
      }
    }

    $ctx->add_namespace($union_symbol, $union_scope);
  }

  private static function let_stmt(self $ctx, nodes\LetStmt $stmt): void {
    $let_name   = $stmt->name->value;
    $let_symbol = $ctx->make_var_symbol($stmt->name);
    $ctx->current_block_scope()->add_binding($let_name, $let_symbol);
  }

  private static function enter_block(self $ctx): void {
    $block_scope = new Scope();
    $ctx->push_block_scope($block_scope);
  }

  private static function exit_block(self $ctx): void {
    $ctx->pop_block_scope();
  }

  private static function enter_match_arm(self $ctx): void {
    $block_scope = new Scope();
    $ctx->push_block_scope($block_scope);
  }

  private static function exit_match_arm(self $ctx): void {
    $ctx->pop_block_scope();
  }

  private static function exit_variant_pattern(self $ctx, nodes\VariantPattern $pattern): void {
    $fields = $pattern->fields;
    if ($fields instanceof nodes\NamedVariantPatternFields) {
      $union_symbol = $pattern->ref->tail_segment->get('symbol');
      // TODO: user error if this lookup fails
      $union_namespace = $ctx->get_namespace($union_symbol);
      foreach ($fields->mapping as $field) {
        $field_name   = $field->name->value;
        $field_symbol = $union_namespace->get_name($field_name);
        // TODO: user error if this lookup fails
        assert($field_symbol !== null);
        $ctx->set_symbol($field->name, $field_symbol);
      }
    }
  }

  private static function variable_pattern(self $ctx, nodes\VariablePattern $pattern): void {
    $name   = $pattern->name->value;
    $symbol = $ctx->make_var_symbol($pattern->name);
    $ctx->current_block_scope()->add_binding($name, $symbol);
  }

  /**
   * @param Resolve                             $ctx
   * @param nodes\NamedVariantConstructorFields $fields
   * @param ir\Path                             $path
   * @throws Error
   */
  private static function named_variant_constructor_fields(self $ctx, nodes\NamedVariantConstructorFields $fields, ir\Path $path): void {
    $expr = $path->parent->node;
    assert($expr instanceof nodes\VariantConstructorExpr);
    $ctor_symbol = $expr->ref->tail_segment->get('symbol');
    if ($ctor_namespace = $ctx->get_namespace($ctor_symbol)) {
      foreach ($fields->pairs as $field) {
        if ($field_symbol = $ctor_namespace->get_name($field->name->value)) {
          $ctx->set_symbol($field->name, $field_symbol);
        } else {
          throw Errors::unknown_constructor_field($field->name->get('span'), $expr->ref, $field->name);
        }
      }
    } else {
      throw Errors::unknown_constructor_form($expr->get('span'), $expr->ref);
    }
  }

  /**
   * @param Resolve         $ctx
   * @param nodes\ParamNote $note
   * @throws Error
   */
  private static function param_note(self $ctx, nodes\ParamNote $note): void {
    if ($ctx->has_param_scope() === false) {
      throw Errors::type_param_used_outside_function($note->get('span'));
    }

    $param_scope = $ctx->current_param_scope();
    if ($param_symbol = $param_scope->get_name($note->name)) {
      assert($param_symbol instanceof TypeSymbol);
      $ctx->set_symbol($note->name, $param_symbol);
    } else {
      throw Errors::unknown_type_param($note->get('span'), $note);
    }
  }

  /**
   * @param Resolve   $ctx
   * @param nodes\Ref $ref
   * @throws Error
   */
  private static function ref(self $ctx, nodes\Ref $ref): void {
    $is_extern     = $ref->extern;
    $head_segments = $ref->head_segments;
    $tail_segment  = $ref->tail_segment;

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
          [ $ctx->current_func_scope() ]
        );
        foreach ($scopes as $scope) {
          if ($tail_symbol = $scope->get_name($tail_name)) {
            $ctx->set_symbol($tail_segment, $tail_symbol);
            return;
          }
        }
      }

      if ($ctx->has_func_scope()) {
        if ($tail_symbol = $ctx->current_func_scope()->get_name($tail_name)) {
          // If the reference exists inside of a function signature or if the
          // function body does not contain the name.
          $ctx->set_symbol($tail_segment, $tail_symbol);
          return;
        }
      }

      if ($tail_symbol = $ctx->current_module_scope()->get_name($tail_name)) {
        // If the reference exists outside of a block scope or if none of the
        // current blocks scopes contain the name, try looking in the closest
        // module scope.
        $ctx->set_symbol($tail_segment, $tail_symbol);
        return;
      }

      throw Errors::unknown_name($tail_segment->get('span'), $tail_segment);
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

        throw Errors::unknown_namespace_field($head_segment->get('span'), $head_segment);
      }

      $tail_name = $tail_segment->value;
      if ($tail_symbol = $scope->get_name($tail_name)) {
        $ctx->set_symbol($tail_segment, $tail_symbol);
      } else {
        throw Errors::unknown_namespace_field($tail_segment->get('span'), $tail_segment);
      }
    }
  }
}
