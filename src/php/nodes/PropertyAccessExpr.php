<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class PropertyAccessExpr extends Expr {
  public $expr;
  public $prop;

  function __construct(Expr $expr, Variable $prop) {
    parent::__construct();
    $this->expr = $expr;
    $this->prop = $prop;
  }

  function to_children(): array {
    return [
      $this->expr,
      $this->prop,
    ];
  }

  function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1]);
  }

  function build(): Builder {
    return (new Builder)
      ->then($this->expr)
      ->thin_arrow()
      ->identifier($this->prop->value);
  }
}
