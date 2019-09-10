<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\IR;
use Cthulhu\Kernel\Kernel;
use Cthulhu\Parser\Lexer\Span;
use Cthulhu\Source;
use Cthulhu\Types;

class Context {
  public $file;
  public $used_builtins;
  private $builtin_cache;
  private $module_scopes;
  private $expected_return;

  function __construct(Source\File $file) {
    $this->file = $file;
    $this->used_builtins = [];
    $this->builtin_cache = [
      'IO' => Kernel::IO()
    ];

    $this->module_scopes = [
      new IR\ModuleScope(null, $file->basename())
    ];

    $this->block_scopes = [
      // empty
    ];

    $this->expected_return = [];
  }

  function current_module_scope(): IR\ModuleScope {
    return end($this->module_scopes);
  }

  function push_module_scope(AST\IdentNode $name): IR\ModuleScope {
    $parent = $this->current_module_scope();
    $child = new IR\ModuleScope($parent, $name->ident);
    $parent->add($child->symbol, $chidl);
    return $this->module_scopes[] = $child;
  }

  function pop_module_scope(): IR\ModuleScope {
    return array_pop($this->module_scopes);
  }

  function resolve_module_scope(string $name): IR\ModuleScope {
    switch ($name) {
      case 'IO':
        $this->used_builtins[] = $this->builtin_cache['IO'];
        return $this->builtin_cache['IO']->scope;
      default:
          throw new \Exception('no known module named ' . $name);
    }
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

  function push_expected_return(AST\Node $fn_node, Types\Type $return_type): void {
    $this->expected_return[] = [$fn_node, $return_type];
  }

  function current_expected_return(): array {
    return end($this->expected_return);
  }

  function pop_expected_return(): void {
    array_pop($this->expected_return);
  }
}
