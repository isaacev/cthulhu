<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\loc\Spanlike;

class ListExpr extends Expr {
  public array $elements;

  /**
   * @param Spanlike $spanlike
   * @param Expr[]   $elements
   */
  public function __construct(Spanlike $spanlike, array $elements) {
    parent::__construct($spanlike);
    $this->elements = $elements;
  }

  public function build(): Builder {
    return (new Builder)
      ->bracket_left()
      ->each($this->elements, (new Builder)
        ->space())
      ->bracket_right();
  }
}
