<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class FlatArrayExpr extends Expr {
  public $elements;

  function __construct(array $elements) {
    parent::__construct();
    $this->elements = $elements;
  }

  public function to_children(): array {
    return $this->elements;
  }

  public function from_children(array $nodes): Node {
    return new self($nodes);
  }

  public function build(): Builder {
    return (new Builder)
      ->operator('[')
      ->each($this->elements, (new Builder)
        ->comma()
        ->space())
      ->operator(']');
  }
}
