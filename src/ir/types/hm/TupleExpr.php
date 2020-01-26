<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\loc\Spanlike;

class TupleExpr extends Expr {
  public array $fields;

  /**
   * @param Spanlike $spanlike
   * @param Expr[]   $fields
   */
  public function __construct(Spanlike $spanlike, array $fields) {
    parent::__construct($spanlike);
    $this->fields = $fields;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('tuple')
      ->space()
      ->each($this->fields, (new Builder)
        ->space())
      ->paren_right();
  }
}
