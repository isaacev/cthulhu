<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class SubscriptExpr extends Expr {
  public Expr $arr;
  public Expr $index;

  public function __construct(Expr $arr, Expr $index) {
    parent::__construct();
    $this->arr   = $arr;
    $this->index = $index;
  }

  public function to_children(): array {
    return [ $this->arr, $this->index ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1]);
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->arr, $this->precedence())
      ->operator('[')
      ->expr($this->index)
      ->operator(']');
  }
}
