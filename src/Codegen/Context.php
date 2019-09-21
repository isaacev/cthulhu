<?php

namespace Cthulhu\Codegen;

class Context {
  public $namespaces;
  public $block_stack;
  public $renamer;

  function __construct() {
    $this->namespaces = [];
    $this->namespace_stack = [];
    $this->block_stack = [];
    $this->renamer = new Renamer();
    $this->renamer->push_scope(new PHP\NamespaceScope(null));
  }

  function push_namespace(PHP\Reference $name) {
    $this->namespace_stack[] = [
      'name' => $name,
      'stmts' => []
    ];
  }

  function push_stmt_to_namespace(PHP\Stmt $stmt) {
    $this->namespace_stack[count($this->namespace_stack) - 1]['stmts'][] = $stmt;
  }

  function pop_namespace() {
    $popped = array_pop($this->namespace_stack);
    $block = new PHP\BlockNode($popped['stmts']);
    $this->namespaces[] = new PHP\NamespaceNode($popped['name'], $block);
  }

  function push_block() {
    return $this->block_stack[] = [];
  }

  function pop_block(): PHP\BlockNode {
    return new PHP\BlockNode(array_pop($this->block_stack));
  }

  function push_stmt_to_block(PHP\Stmt $stmt) {
    $this->block_stack[count($this->block_stack) - 1][] = $stmt;
  }
}
