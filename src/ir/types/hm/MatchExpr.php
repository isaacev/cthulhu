<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\loc\Spanlike;

class MatchExpr extends Expr {
  public Expr $discriminant;
  public array $arms;

  /**
   * @param Spanlike $spanlike
   * @param Expr     $discriminant
   * @param Arm[]    $arms
   */
  public function __construct(Spanlike $spanlike, Expr $discriminant, array $arms) {
    parent::__construct($spanlike);
    $this->discriminant = $discriminant;
    $this->arms         = $arms;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('match')
      ->space()
      ->then($this->discriminant)
      ->increase_indentation()
      ->newline()
      ->indent()
      ->each($this->arms, (new Builder)
        ->newline()
        ->indent())
      ->decrease_indentation()
      ->paren_right();
  }
}
