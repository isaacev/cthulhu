<?php

namespace Cthulhu\ir\nodes;

abstract class Stmts extends Node {
  public ?Stmt $first;

  public function __construct(?Stmt $first) {
    parent::__construct();
    $this->first = $first;
  }

  public function children(): array {
    return [ $this->first ];
  }

  public function build(): Builder {
    if ($this->first) {
      return (new Builder)
        ->paren_left()
        ->increase_indentation()
        ->then($this->first)
        ->decrease_indentation()
        ->paren_right();
    }

    return (new Builder)
      ->paren_left()
      ->paren_right();
  }
}
