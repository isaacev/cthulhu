<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;
use Cthulhu\lib\trees\EditableNodelike;

class Intrinsic extends Expr {
  public string $ident;
  public Exprs $args;

  public function __construct(Type $type, string $ident, Exprs $args) {
    parent::__construct($type);
    $this->ident = $ident;
    $this->args  = $args;
  }

  public function children(): array {
    return [ $this->args ];
  }

  public function from_children(array $children): EditableNodelike {
    return (new self($this->type, $this->ident, $children[0]))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('intrinsic')
      ->space()
      ->ident($this->ident)
      ->colon()
      ->type($this->type)
      ->space()
      ->paren_left()
      ->increase_indentation()
      ->then($this->args)
      ->decrease_indentation()
      ->paren_right()
      ->paren_right();
  }
}
