<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class FieldNode extends Node {
  public $name;
  public $expr;

  function __construct(Name $name, Expr $expr) {
    parent::__construct();
    $this->name = $name;
    $this->expr = $expr;
  }

  function to_children(): array {
    return [
      $this->name,
      $this->expr,
    ];
  }

  function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1]);
  }

  function build(): Builder {
    return (new Builder)
      ->string_literal($this->name->value)
      ->space()
      ->fat_arrow()
      ->space()
      ->then($this->expr);
  }
}
