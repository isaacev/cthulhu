<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class SubscriptExpr extends Expr {
  public $arr;
  public $index;

  function __construct(Expr $arr, Expr $index) {
    parent::__construct();
    $this->arr = $arr;
    $this->index = $index;
  }

  public function to_children(): array {
    return [ $this->arr, $this->index ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1]);
  }

  public function precedence(): int {
    return 50; // TODO: is this correct?
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->arr, $this->precedence())
      ->operator('[')
      ->expr($this->index)
      ->operator(']');
  }
}