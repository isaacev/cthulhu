<?php

namespace Cthulhu\ast;

use Cthulhu\err\Error;
use Cthulhu\ir\names\Binding;
use Cthulhu\ir\names\OperatorBinding;
use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\names\Scope;
use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\names\TypeSymbol;
use Cthulhu\lib\trees\Visitor;

class ShallowResolver {
  private Scope $root_scope;

  /* @var Scope[] $namespaces */
  private array $namespaces;

  /* @var Scope[] $modules */
  private array $module_scopes = [];

  /* @var RefSymbol[] $ref_path */
  private array $ref_symbols = [];

  private function make_ref_symbol_for_name(nodes\Name $node, ?RefSymbol $parent): RefSymbol {
    $symbol = new RefSymbol($parent);
    $this->set_symbol($node, $symbol);
    $symbol->set('node', $node);
    $symbol->set('text', $node->value);
    return $symbol;
  }

  private function make_ref_symbol_for_oper(nodes\Operator $node, ?RefSymbol $parent): RefSymbol {
    $symbol = new RefSymbol($parent);
    $this->set_symbol($node, $symbol);
    $symbol->set('node', $node);
    $symbol->set('text', '(' . $node->value . ')');
    return $symbol;
  }

  private function make_type_symbol(nodes\UpperName $node): TypeSymbol {
    $symbol = new TypeSymbol();
    $this->set_symbol($node, $symbol);
    $symbol->set('node', $node);
    $symbol->set('text', $node->value);
    return $symbol;
  }

  private function set_symbol(nodes\Node $node, Symbol $symbol): void {
    $node->set('symbol', $symbol);
  }

  private function current_module_scope(): Scope {
    return end($this->module_scopes);
  }

  private function push_module_scope(Scope $scope): void {
    array_push($this->module_scopes, $scope);
  }

  private function pop_module_scope(): Scope {
    return array_pop($this->module_scopes);
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

  private function link_prelude(): void {
    $extern_namespace  = $this->root_scope();
    $prelude_namespace = $this->get_namespace($extern_namespace->get_name('Prelude')->symbol);
    foreach ($prelude_namespace->get_public_bindings() as $name => $binding) {
      $this->current_module_scope()->add_binding($binding->as_private());
    }
  }

  public static function resolve(nodes\ShallowProgram $prog): void {
    $ctx = new self();

    Visitor::walk($prog, [
      'enter(ShallowProgram)' => function () use ($ctx) {
        self::enter_program($ctx);
      },
      'enter(ShallowFile)' => function (nodes\ShallowFile $file) use ($ctx) {
        self::enter_file($ctx, $file);
      },
      'exit(ShallowFile)' => function () use ($ctx) {
        self::exit_file($ctx);
      },
      'ShallowEnumItem' => function (nodes\ShallowEnumItem $item) use ($ctx) {
        self::enum_item($ctx, $item);
      },
      'ShallowIntrinsicItem' => function (nodes\ShallowIntrinsicItem $item) use ($ctx) {
        self::intrinsic_item($ctx, $item);
      },
      'ShallowUseItem' => function (nodes\ShallowUseItem $item) use ($ctx) {
        self::use_item($ctx, $item);
      },
      'enter(ShallowModItem)' => function (nodes\ShallowModItem $item) use ($ctx) {
        self::enter_mod_item($ctx, $item);
      },
      'exit(ShallowModItem)' => function () use ($ctx) {
        self::exit_mod_item($ctx);
      },
      'enter(ShallowFnItem)' => function (nodes\ShallowFnItem $item) use ($ctx) {
        self::enter_fn_item($ctx, $item);
      },
    ]);
  }

  private static function instantiate_kernel_type(self $ctx, string $type_name): void {
    $type_symbol    = new RefSymbol($ctx->current_ref_symbol());
    $type_is_public = true;
    $type_binding   = new Binding($type_name, $type_symbol, $type_is_public);
    $ctx->current_module_scope()->add_binding($type_binding);
  }

  private static function enter_program(self $ctx): void {
    $ctx->root_scope = new Scope();
  }

  private static function enter_file(self $ctx, nodes\ShallowFile $file): void {
    $lib_name    = $file->name->value;
    $lib_symbol  = $ctx->make_ref_symbol_for_name($file->name, null);
    $lib_binding = new Binding($lib_name, $lib_symbol, true);
    $ctx->root_scope()->add_binding($lib_binding);
    $ctx->push_ref_symbol($lib_symbol);

    $lib_scope = new Scope();
    $lib_symbol->set('scope', $lib_scope);
    $ctx->add_namespace($lib_symbol, $lib_scope);
    $ctx->push_module_scope($lib_scope);

    switch ($file->name->value) {
      case 'Kernel':
        self::instantiate_kernel_type($ctx, 'Bool');
        self::instantiate_kernel_type($ctx, 'Int');
        self::instantiate_kernel_type($ctx, 'Float');
        self::instantiate_kernel_type($ctx, 'Str');
        break;
      case 'Prelude':
        // do nothing
        break;
      default:
        $ctx->link_prelude();
    }
  }

  private static function exit_file(self $ctx): void {
    $ctx->pop_ref_symbol();
    $ctx->pop_module_scope();
  }

  private static function enum_item(self $ctx, nodes\ShallowEnumItem $item): void {
    $enum_is_public = $item->get('pub') ?? false;
    $enum_name      = $item->name->value;
    $enum_symbol    = $ctx->make_ref_symbol_for_name($item->name, $ctx->current_ref_symbol());
    $enum_binding   = new Binding($enum_name, $enum_symbol, $enum_is_public);
    $ctx->current_module_scope()->add_binding($enum_binding);
  }

  private static function intrinsic_item(self $ctx, nodes\ShallowIntrinsicItem $item): void {
    $is_public = $item->get('pub') ?? false;
    foreach ($item->signatures as $signature) {
      $sig_name    = $signature->name;
      $sig_symbol  = $ctx->make_ref_symbol_for_name($sig_name, $ctx->current_ref_symbol());
      $sig_binding = new Binding($sig_name, $sig_symbol, $is_public);
      $ctx->current_module_scope()->add_binding($sig_binding);
    }
  }

  /**
   * @param ShallowResolver      $ctx
   * @param nodes\ShallowUseItem $item
   * @throws Error
   */
  private static function use_item(self $ctx, nodes\ShallowUseItem $item): void {
    $namespace = $item->path->is_extern
      ? $ctx->root_scope()
      : $ctx->current_module_scope();

    foreach ($item->path->body as $segment) {
      $body_name = $segment->value;
      if ($body_binding = $namespace->get_name($body_name)) {
        $ctx->set_symbol($segment, $body_binding->symbol);
        if ($next_namespace = $ctx->get_namespace($body_binding->symbol)) {
          $namespace = $next_namespace;
          continue;
        }
      }

      throw Errors::unknown_namespace_field($segment->get('span'));
    }

    $is_pub = $item->get('pub') ?? false;
    if ($item->path->tail instanceof nodes\StarSegment) {
      foreach ($namespace->get_public_bindings() as $binding) {
        $binding = $is_pub ? $binding : $binding->as_private();
        $ctx->current_module_scope()->add_binding($binding);
      }
    } else {
      $tail_name = $item->path->tail->value;
      if ($tail_binding = $namespace->get_name($tail_name)) {
        $tail_binding = $is_pub ? $tail_binding : $tail_binding->as_private();
        $ctx->set_symbol($item->path->tail, $tail_binding->symbol);
        $ctx->current_module_scope()->add_binding($tail_binding);
      } else {
        throw Errors::unknown_namespace_field($item->path->tail->get('span'));
      }
    }
  }

  private static function enter_mod_item(self $ctx, nodes\ShallowModItem $item): void {
    $mod_name    = $item->name->value;
    $mod_symbol  = $ctx->make_ref_symbol_for_name($item->name, $ctx->current_ref_symbol());
    $mod_binding = new Binding($mod_name, $mod_symbol, $item->get('pub') ?? false);
    $ctx->current_module_scope()->add_binding($mod_binding);
    $ctx->push_ref_symbol($mod_symbol);

    $mod_scope = new Scope();
    $mod_symbol->set('scope', $mod_scope);
    $ctx->add_namespace($mod_symbol, $mod_scope);
    $ctx->push_module_scope($mod_scope);

    $ctx->link_prelude();
  }

  private static function exit_mod_item(self $ctx): void {
    $ctx->pop_ref_symbol();
    $ctx->pop_module_scope();
  }

  private static function enter_fn_item(self $ctx, nodes\ShallowFnItem $item): void {
    $fn_is_public = $item->get('pub') ?? false;
    if ($item->name instanceof nodes\OperatorRef) {
      $fn_oper    = $item->name->oper;
      $fn_symbol  = $ctx->make_ref_symbol_for_oper($fn_oper, $ctx->current_ref_symbol());
      $fn_binding = new OperatorBinding($fn_symbol, $fn_is_public, $fn_oper);
    } else {
      assert($item->name instanceof nodes\LowerName);
      $fn_name    = $item->name->value;
      $fn_symbol  = $ctx->make_ref_symbol_for_name($item->name, $ctx->current_ref_symbol());
      $fn_binding = new Binding($fn_name, $fn_symbol, $fn_is_public);
    }

    $ctx->current_module_scope()->add_binding($fn_binding);
  }
}