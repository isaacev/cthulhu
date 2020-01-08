<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class DynamicPropertyAccessExpr extends Expr {
  public Expr $expr;
  public Expr $prop;

  public function __construct(Expr $expr, Expr $prop) {
    parent::__construct();
    $this->expr = $expr;
    $this->prop = $prop;
  }

  public function to_children(): array {
    return [
      $this->expr,
      $this->prop,
    ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1]);
  }

  public function build(): Builder {
    return (new Builder)
      ->then($this->expr)
      ->thin_arrow()
      ->brace_left()
      ->then($this->prop)
      ->brace_right();
  }
}
