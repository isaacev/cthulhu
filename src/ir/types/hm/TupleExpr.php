<?php

namespace Cthulhu\ir\types\hm;

class TupleExpr extends Expr {
  public array $fields;

  /**
   * @param Expr[] $fields
   */
  public function __construct(array $fields) {
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
