<?php

namespace Cthulhu\Analysis;

use Cthulhu\IR;
use Cthulhu\Types;

class Context {
  private $module_scopes;

  function __construct() {
    $this->module_scopes = [
      new IR\ModuleScope3(null, 'main')
    ];
    $this->block_scopes = [
      // empty
    ];
  }

  function current_module_scope(): IR\ModuleScope3 {
    return end($this->module_scopes);
  }

  function push_module_scope(AST\IdentNode $name): IR\ModuleScope3 {
    $parent = $this->current_module_scope();
    $child = new IR\ModuleScope3($parent, $name->ident);
    $parent->add($child->symbol, $chidl);
    return $this->module_scopes[] = $child;
  }

  function pop_module_scope(): IR\ModuleScope3 {
    return array_pop($this->module_scopes);
  }

  function resolve_module_scope(string $name): IR\ModuleScope3 {
    switch ($name) {
      case 'IO':
        return IR\ModuleScope3::from_array('IO', [
          'println' => new Types\FnType([ new Types\StrType() ], new Types\VoidType())
        ]);
      default:
          throw new \Exception('no known module named ' . $name);
    }
  }

  function current_block_scope(): IR\BlockScope3 {
    return end($this->block_scopes);
  }

  function push_block_scope(): IR\BlockScope3 {
    $parent = empty($this->block_scopes)
      ? $this->current_module_scope()
      : $this->current_block_scope();
    return $this->block_scopes[] = new IR\BlockScope3($parent);
  }

  function pop_block_scope(): IR\BlockScope3 {
    return array_pop($this->block_scopes);
  }
}
