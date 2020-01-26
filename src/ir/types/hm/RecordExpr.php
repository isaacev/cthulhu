<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\loc\Spanlike;

class RecordExpr extends Expr {
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
    $fields = [];
    foreach ($this->fields as $name => $expr) {
      $fields[] = (new Builder)
        ->paren_left()
        ->keyword($name)
        ->space()
        ->then($expr)
        ->paren_right();
    }

    return (new Builder)
      ->paren_left()
      ->keyword('record')
      ->space()
      ->increase_indentation()
      ->newline()
      ->indent()
      ->each($fields, (new Builder)
        ->newline()
        ->indent())
      ->decrease_indentation()
      ->paren_right();
  }
}
