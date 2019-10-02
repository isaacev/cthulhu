<?php

namespace Cthulhu\Codegen;

class Path {
  public $parent;
  public $node;
  public $was_changed = false;
  public $was_removed = false;
  public $new_sibling_nodes = [];

  function __construct(?self $parent, PHP\Node $node) {
    $this->parent = $parent;
    $this->node = $node;
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

  function allows_sibling_nodes(): bool {
    return (
      $this->node instanceof PHP\BlockNode ||
      $this->node instanceof PHP\ProgramNode
    );
  }

  function remove(): void {
    if (($this->node instanceof PHP\Stmt) === false) {
      throw new \Exception('Path#remove can only be called on statement nodes');
    }

    $this->was_changed = true;
    $this->was_removed = true;
    $this->node = null;
  }

  function replace_with(PHP\Node $node) {
    if ($this->was_removed()) {
      throw new \Exception('cannot replace node after it was removed');
    }
    $this->was_changed = true;
    $this->node = $node;
  }

  function after(array $nodes) {
    if (($this->node instanceof PHP\Stmt) === false) {
      throw new \Exception('Path#remove can only be called on statement nodes');
    } else if ($this->was_removed()) {
      throw new \Exception('cannot add sibilngs to a node after it was removed');
    }
    $this->was_changed = true;
    $this->new_sibling_nodes = $nodes;
  }

  function replace_with_multiple(array $nodes) {
    if (($this->node instanceof PHP\Stmt) === false) {
      throw new \Exception('Path#remove can only be called on statement nodes');
    } else if (empty($nodes)) {
      $this->remove();
    } else {
      $this->replace_with($nodes[0]);
      $this->after(array_slice($nodes, 1));
    }
  }
}
