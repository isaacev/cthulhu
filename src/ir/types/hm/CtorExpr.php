<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ir\names\RefSymbol;

class CtorExpr extends Expr {
  public RefSymbol $enum_symbol;
  public RefSymbol $form_symbol;
  public Expr $args;

  public function __construct(RefSymbol $enum_symbol, RefSymbol $form_symbol, Expr $args) {
    $this->enum_symbol = $enum_symbol;
    $this->form_symbol = $form_symbol;
    $this->args        = $args;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('ctor')
      ->space()
      ->name($this->enum_symbol)
      ->space()
      ->name($this->form_symbol)
      ->space()
      ->then($this->args)
      ->paren_right();
  }
}
