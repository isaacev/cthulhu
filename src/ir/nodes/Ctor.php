<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\types\hm\Type;
use Cthulhu\lib\trees\EditableNodelike;

class Ctor extends Expr {
  public RefSymbol $form_symbol;
  public Expr $args;

  public function __construct(Type $type, RefSymbol $form_symbol, Expr $args) {
    parent::__construct($type);
    $this->form_symbol = $form_symbol;
    $this->args        = $args;
  }

  public function children(): array {
    return [ $this->args ];
  }

  public function from_children(array $children): EditableNodelike {
    return new self($this->type, $this->form_symbol, $children[0]);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('ctor')
      ->keyword($this->form_symbol)
      ->space()
      ->then($this->args)
      ->paren_right();
  }
}
