<?php

namespace Cthulhu\ir\nodes;

class ListExpr extends Expr {
  public array $elements;

  /**
   * @param Expr[] $elements
   */
  function __construct(array $elements) {
    parent::__construct();
    $this->elements = $elements;
  }

  function children(): array {
    return $this->elements;
  }
}
