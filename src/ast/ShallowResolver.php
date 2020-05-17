<?php

namespace Cthulhu\ast;

use Cthulhu\err\Error;
use Cthulhu\ir\names\Binding;
use Cthulhu\ir\names\ModuleBinding;
use Cthulhu\ir\names\OperatorBinding;
use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\names\Scope;
use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\names\TermBinding;
use Cthulhu\lib\trees\Visitor;

class ShallowResolver {
  private bool $is_in_prelude = false;

  private Scope $root_scope;

  /* @var Scope[] $namespaces */
  private array $namespaces;

  /* @var Scope[] $modules */
  private array $module_scopes = [];

  /* @var RefSymbol[] $ref_path */
  private array $ref_symbols = [];

  /* @var Binding[] $synthetic_types */
  private array $synthetic_types = [];

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
    return $symbol
      ->set('node', $node)
      ->set('text', '(' . $node->value . ')')
      ->set('operator', $node->value);
  }

  private function set_symbol(nodes\Node $node, Symbol $symbol): void {
    $node->set('symbol', $symbol);
  }

  private function super_module_scope(int $levels): ?Scope {
    assert($levels >= 0);
    $index = count($this->module_scopes) - 1 - $levels;
    if ($index < 0) {
      return null;
    } else {
      return $this->module_scopes[$index];
    }
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

  private function super_ref_symbol(int $levels): ?RefSymbol {
    assert($levels >= 0);
    $index = count($this->ref_symbols) - 1 - $levels;
    if ($index < 0) {
      return null;
    } else {
      return $this->ref_symbols[$index];
    }
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
    $prelude_namespace = $this->get_namespace($extern_namespace->get_module_binding('Prelude')->symbol);

    foreach ($prelude_namespace->all_public_module_bindings() as $name => $binding) {
      $this->current_module_scope()->add_module_binding($binding->as_private());
    }

    foreach ($prelude_namespace->all_public_term_bindings() as $name => $binding) {
      $this->current_module_scope()->add_term_binding($binding->as_private());
    }
  }

  /**
   * @param nodes\ShallowProgram $prog
   * @return Binding[]
   */
  public static function resolve(nodes\ShallowProgram $prog): array {
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

    return $ctx->synthetic_types;
  }

  private static function instantiate_kernel_type(self $ctx, string $type_name): void {
    $type_symbol    = new RefSymbol($ctx->current_ref_symbol());
    $type_is_public = true;
    $type_binding   = new TermBinding($type_name, $type_symbol, $type_is_public);
    $ctx->current_module_scope()->add_term_binding($type_binding);
    $ctx->synthetic_types[] = $type_binding;
  }

  private static function enter_program(self $ctx): void {
    $ctx->root_scope = new Scope();
  }

  private static function enter_file(self $ctx, nodes\ShallowFile $file): void {
    $lib_name    = $file->name->value;
    $lib_symbol  = $ctx->make_ref_symbol_for_name($file->name, null);
    $lib_binding = new ModuleBinding($lib_name, $lib_symbol, true);
    $ctx->root_scope()->add_module_binding($lib_binding);
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
        $ctx->is_in_prelude = true;
        break;
      default:
        $ctx->link_prelude();
    }
  }

  private static function exit_file(self $ctx): void {
    $ctx->is_in_prelude = false;
    $ctx->pop_ref_symbol();
    $ctx->pop_module_scope();
  }

  private static function enum_item(self $ctx, nodes\ShallowEnumItem $item): void {
    $enum_is_public = $item->get('pub') ?? false;
    $enum_name      = $item->name->value;
    $enum_symbol    = $ctx->make_ref_symbol_for_name($item->name, $ctx->current_ref_symbol());
    $enum_binding   = new TermBinding($enum_name, $enum_symbol, $enum_is_public);
    $ctx->current_module_scope()->add_term_binding($enum_binding);

    foreach ($item->forms as $form) {
      $form_symbol  = $ctx->make_ref_symbol_for_name($form->name, $enum_symbol);
      $form_binding = new TermBinding($form->name->value, $form_symbol, $enum_is_public);
      $ctx->current_module_scope()->add_term_binding($form_binding);

      // Give the form symbol a reference to the enum symbol. This will
      // make it easier to lookup the enum type during typechecking.
      $form_symbol->set('enum', $enum_symbol);
    }
  }

  private static function intrinsic_item(self $ctx, nodes\ShallowIntrinsicItem $item): void {
    $is_public = $item->get('pub') ?? false;
    foreach ($item->signatures as $signature) {
      $sig_name    = $signature->name;
      $sig_symbol  = $ctx->make_ref_symbol_for_name($sig_name, $ctx->current_ref_symbol());
      $sig_binding = new TermBinding($sig_name, $sig_symbol, $is_public);
      $ctx->current_module_scope()->add_term_binding($sig_binding);
    }
  }

  /**
   * @param ShallowResolver      $ctx
   * @param nodes\ShallowUseItem $item
   * @throws Error
   */
  private static function use_item(self $ctx, nodes\ShallowUseItem $item): void {
    $is_pub = $item->get('pub') ?? false;

    // Accumulate a string representation of the longest
    // valid path for use in any error reports.
    $longest_valid_path = '';

    /* Bind each head segment in the path to a module.
     */

    if ($item->path->is_extern) {
      $namespace = $ctx->root_scope();
    } else if (empty($item->path->super)) {
      $namespace = $ctx->current_module_scope();
    } else {
      $namespace = $ctx->super_module_scope(count($item->path->super));
      if ($namespace === null) {
        throw new Error('too many super references'); // TODO
      }

      foreach ($item->path->super as $index => $super_segment) {
        $mod_symbol = $ctx->super_ref_symbol(count($item->path->super) - $index);

        if ($mod_symbol) {
          $ctx->set_symbol($super_segment, $mod_symbol);
          $longest_valid_path .= ($index === 0) ? 'super' : '::super';
        } else {
          throw new Error("too many super references");
        }
      }
    }

    foreach ($item->path->head as $index => $body_segment) {
      $mod_binding = ($namespace === $ctx->current_module_scope())
        ? $namespace->get_module_binding($body_segment->value)
        : $namespace->get_public_module_binding($body_segment->value);

      if ($mod_binding) {
        $ctx->set_symbol($body_segment, $mod_binding->symbol);
        if ($next_namespace = $ctx->get_namespace($mod_binding->symbol)) {
          $longest_valid_path .= $index == 0
            ? $mod_binding->name
            : "::$mod_binding->name";
          $namespace          = $next_namespace;
          continue;
        } else {
          // The parent namespace _has_ a matching binding but that binding does
          // not correspond to a namespace (meaning it's a reference to a
          // function or a type)
          throw new Error("unknown name"); // TODO
        }
      }

      $spanlike       = $body_segment->get('span');
      $field_name     = $body_segment->value;
      $namespace_name = $longest_valid_path;
      $fixes          = ($namespace === $ctx->current_module_scope())
        ? $namespace->all_public_and_private_module_names()
        : $namespace->all_public_module_names();
      throw Errors::unknown_namespace_field($spanlike, $field_name, $namespace_name, $fixes);
    }

    if ($namespace === $ctx->current_module_scope() && $item->path->tail instanceof nodes\Name) {
      $tail_name    = $item->path->tail->value;
      $term_binding = $namespace->get_public_or_private_term_binding($tail_name);
      $mod_binding  = $namespace->get_module_binding($tail_name);

      if (!$term_binding && !$mod_binding) {
        $spanlike = $item->path->tail->get('span');
        $fixes    = []; // TODO
        throw Errors::unknown_namespace_field_in_current_scope($spanlike, $tail_name, $fixes);
      }

      // Do nothing because the item is just importing bindings that are
      // already in the current module scope.
      return;
    }

    /* If the path ends in a star segment, bind all of the modules and terms
     * from the path to the current module scope.
     */
    if ($item->path->tail instanceof nodes\StarSegment) {
      foreach ($namespace->all_public_module_bindings() as $mod_binding) {
        $mod_binding = $is_pub ? $mod_binding : $mod_binding->as_private();
        $ctx->current_module_scope()->add_module_binding($mod_binding);
      }

      foreach ($namespace->all_public_term_bindings() as $term_binding) {
        $term_binding = $is_pub ? $term_binding : $term_binding->as_private();
        $ctx->current_module_scope()->add_term_binding($term_binding);
      }

      return;
    }

    assert($item->path->tail instanceof nodes\Name);

    /* Bind the tail segment to either a term OR a module. The following
     * cases are possible and all must be supported:
     *
     * - Tail segment matches ONLY a term:
     *     Attach the term symbol to the segment and add
     *     the term binding to the current module scope.
     *
     * - Tail segment matches ONLY a module
     *     Attach the module symbol to the segment and add
     *     a binding for the referenced module to the
     *     current module scope.
     *
     * - Tail segment matches BOTH a term and a module
     *     Attach the type symbol to the segment and add
     *     a binding for BOTH the referenced module and
     *     the referenced type to the current module scope.
     */
    $tail_name    = $item->path->tail->value;
    $mod_binding  = $namespace->get_public_module_binding($tail_name);
    $term_binding = $namespace->get_public_term_binding($tail_name);

    if ($mod_binding && $term_binding) {
      // Unless the `use` item is marked as public, mark the local bindings as private
      $mod_binding  = $is_pub ? $mod_binding : $mod_binding->as_private();
      $term_binding = $is_pub ? $term_binding : $term_binding->as_private();

      // Attach the term symbol to the tail segment
      $ctx->set_symbol($item->path->tail, $term_binding->symbol);

      // Add both bindings to the current module scope
      $ctx->current_module_scope()->add_module_binding($mod_binding);
      $ctx->current_module_scope()->add_term_binding($term_binding);
    } else if ($mod_binding) {
      // Unless the `use` item is marked as public, mark the local binding as private
      $mod_binding = $is_pub ? $mod_binding : $mod_binding->as_private();

      // Attach the module symbol to the tail segment
      $ctx->set_symbol($item->path->tail, $mod_binding->symbol);

      // Add the module binding to the current module scope
      $ctx->current_module_scope()->add_module_binding($mod_binding);
    } else if ($term_binding) {
      // Unless the `use` item is marked as public, mark the local binding as private
      $term_binding = $is_pub ? $term_binding : $term_binding->as_private();

      // Attach the term symbol to the tail segment
      $ctx->set_symbol($item->path->tail, $term_binding->symbol);

      // Add the term binding to the current module scope
      $ctx->current_module_scope()->add_term_binding($term_binding);
    } else {
      // The tail segment references an unknown module or term. When reporting
      // this error, try to suggest any similarly named modules or terms.
      $spanlike = $item->path->tail->get('span');
      $fixes    = [];
      throw Errors::unknown_namespace_field($spanlike, $tail_name, $longest_valid_path, $fixes);
    }
  }

  private static function enter_mod_item(self $ctx, nodes\ShallowModItem $item): void {
    $mod_name    = $item->name->value;
    $mod_symbol  = $ctx->make_ref_symbol_for_name($item->name, $ctx->current_ref_symbol());
    $mod_binding = new ModuleBinding($mod_name, $mod_symbol, $item->get('pub') ?? false);
    $ctx->current_module_scope()->add_module_binding($mod_binding);
    $ctx->push_ref_symbol($mod_symbol);

    $mod_scope = new Scope();
    $mod_symbol->set('scope', $mod_scope);
    $ctx->add_namespace($mod_symbol, $mod_scope);
    $ctx->push_module_scope($mod_scope);

    if ($ctx->is_in_prelude === false) {
      $ctx->link_prelude();
    }
  }

  private static function exit_mod_item(self $ctx): void {
    $ctx->pop_ref_symbol();
    $ctx->pop_module_scope();
  }

  private static function enter_fn_item(self $ctx, nodes\ShallowFnItem $item): void {
    $fn_is_public = $item->get('pub') ?? false;
    if ($item->name instanceof nodes\Operator) {
      $fn_oper    = $item->name;
      $fn_symbol  = $ctx->make_ref_symbol_for_oper($fn_oper, $ctx->current_ref_symbol());
      $fn_binding = new OperatorBinding($fn_symbol, $fn_is_public, $fn_oper);
    } else {
      assert($item->name instanceof nodes\LowerName);
      $fn_name    = $item->name->value;
      $fn_symbol  = $ctx->make_ref_symbol_for_name($item->name, $ctx->current_ref_symbol());
      $fn_binding = new TermBinding($fn_name, $fn_symbol, $fn_is_public);
    }

    $ctx->current_module_scope()->add_term_binding($fn_binding);
  }
}
