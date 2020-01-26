<?php

namespace Cthulhu\ast\nodes;

class ListExpr extends Expr {
  public array $elements;

  /**
   * @param Expr[] $elements
   */
  public function __construct(array $elements) {
    parent::__construct();
    $this->elements = $elements;
  }

  public function children(): array {
    return $this->elements;
  }
}
