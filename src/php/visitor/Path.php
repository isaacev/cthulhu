<?php

namespace Cthulhu\php\visitor;

use Cthulhu\php;
use Exception;

class Path {
  public $parent;
  public $node;
  public $was_changed = false;
  public $was_removed = false;
  public $new_sibling_nodes = [];

  function __construct(?self $parent, php\nodes\Node $node) {
    $this->parent = $parent;
    $this->node   = $node;
  }

  function was_changed(): bool {
    if ($this->was_changed) {
      return true;
    } else if ($this->parent !== null) {
      return $this->parent->was_changed();
    } else {
      return false;
    }
  }

  function was_removed(): bool {
    if ($this->was_removed) {
      return true;
    } else if ($this->parent !== null) {
      return $this->parent->was_removed();
    } else {
      return false;
    }
  }

  protected function not_stmt_or_namespace(): bool {
    return !(
      $this->node instanceof php\nodes\Stmt ||
      $this->node instanceof php\nodes\NamespaceNode
    );
  }

  function remove(): void {
    if ($this->not_stmt_or_namespace()) {
      throw new Exception('can only remove php\nodes\Stmt and php\nodes\NamespaceNode nodes');
    }

    $this->was_changed = true;
    $this->was_removed = true;
    $this->node        = null;
  }

  function replace_with(php\nodes\Node $node) {
    if ($this->was_removed()) {
      throw new Exception('cannot replace node after it was removed');
    }
    $this->was_changed = true;
    $this->node        = $node;
  }

  function after(array $nodes) {
    if ($this->not_stmt_or_namespace()) {
      throw new Exception('can only add siblings after php\nodes\Stmt and php\nodes\NamespaceNode nodes');
    } else if ($this->was_removed()) {
      throw new Exception('cannot add sibilngs to a node after it was removed');
    }
    $this->was_changed       = true;
    $this->new_sibling_nodes = $nodes;
  }

  function replace_with_multiple(array $nodes) {
    if ($this->not_stmt_or_namespace()) {
      throw new Exception('can only replace php\nodes\Stmt and php\nodes\NamespaceNode with multiple nodes');
    } else if (empty($nodes)) {
      $this->remove();
    } else {
      $this->replace_with($nodes[0]);
      $this->after(array_slice($nodes, 1));
    }
  }
}
