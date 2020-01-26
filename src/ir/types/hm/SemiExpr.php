<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\loc\Spanlike;

class SemiExpr extends Expr {
  public Expr $body;

  public function __construct(Spanlike $spanlike, Expr $body) {
    parent::__construct($spanlike);
    $this->body = $body;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->then($this->body)
      ->paren_right();
  }
}
