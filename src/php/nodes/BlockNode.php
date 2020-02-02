<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class BlockNode extends Node {
  public ?Stmt $stmt;

  public function __construct(?Stmt $stmt) {
    parent::__construct();
    $this->stmt = $stmt;
  }

  public function children(): array {
    return [ $this->stmt ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0]);
  }

  public function build(): Builder {
    return (new Builder)
      ->brace_left()
      ->increase_indentation()
      ->then($this->stmt ?? (new Builder)
          ->newline_then_indent()
          ->comment('empty'))
      ->decrease_indentation()
      ->newline_then_indent()
      ->brace_right();
  }
}
