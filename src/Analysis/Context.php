<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\IR;
use Cthulhu\Source;

class Context {
  public $linker;
  public $file;
  private $module_scopes = [];
  private $block_scopes = [];
  private $generic_tables = [];
  private $expected_return = [];

  function __construct(Linker $linker, Source\File $file) {
    $this->linker = $linker;
    $this->file = $file;

    $this->module_scopes = [
      new IR\ModuleScope(null, $file->basename())
    ];
  }

  function extern_scope(): Linker {
    return $this->linker; // TODO
  }

  function current_module_scope(): IR\ModuleScope {
    return end($this->module_scopes);
  }

  function push_module_scope(AST\IdentNode $name): IR\ModuleScope {
    $parent = $this->current_module_scope();
    $child = new IR\ModuleScope($parent, $name->ident);
    $parent->add_binding(IR\Binding::for_module($child));
    return $this->module_scopes[] = $child;
  }

  function pop_module_scope(): IR\ModuleScope {
    return array_pop($this->module_scopes);
  }

  function has_block_scopes(): bool {
    return !empty($this->block_scopes);
  }

  function current_block_scope(): IR\BlockScope {
    return end($this->block_scopes);
  }

  function push_block_scope(): IR\BlockScope {
    $parent = empty($this->block_scopes)
      ? $this->current_module_scope()
      : $this->current_block_scope();
    return $this->block_scopes[] = new IR\BlockScope($parent);
  }

  function pop_block_scope(): IR\BlockScope {
    return array_pop($this->block_scopes);
  }

  function can_access_generics(): bool {
    return empty($this->generic_tables) === false;
  }

  function push_generic_table(array $table): void {
    $this->generic_tables[] = $table;
  }

  function lookup_generic(string $name): ?IR\Types\Type {
    for ($i = count($this->generic_tables) - 1; $i >= 0; $i--) {
      $table = $this->generic_tables[$i];
      if (array_key_exists($name, $table)) {
        return $table[$name];
      }
    }
    return null;
  }

  function pop_generic_table(): void {
    array_pop($this->generic_tables);
  }

  function push_expected_return(AST\Node $fn_node, IR\Types\Type $return_type): void {
    $this->expected_return[] = [$fn_node, $return_type];
  }

  function current_expected_return(): array {
    return end($this->expected_return);
  }

  function pop_expected_return(): void {
    array_pop($this->expected_return);
  }

  function raw_path_to_binding(string ...$segments): IR\Binding {
    $starting_scope = $this->linker;
    $total_segments = count($segments);
    $intermediate_scope = $starting_scope;
    for ($i = 0; $i < $total_segments; $i++) {
      $segment = $segments[$i];
      $is_last_segment = $i + 1 === $total_segments;

      if ($is_last_segment) {
        if ($binding = $intermediate_scope->resolve_name($segment)) {
          return $binding;
        } else {
          goto fail;
        }
      }

      $binding = $intermediate_scope->resolve_name($segment);
      if ($binding === null) {
        goto fail;
      }

      if ($binding->kind === 'module') {
        $intermediate_scope = $binding->as_scope();
        continue;
      }

      goto fail;
    }

    fail:
    throw new \Exception('unknown path: `::' . implode('::', $segments) . '`');
  }

  function raw_path_to_type(string ...$segments): IR\Types\Type {
    return $this->raw_path_to_binding(...$segments)->as_type();
  }
}
