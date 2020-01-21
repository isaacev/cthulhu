<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\hm\Type;
use Cthulhu\lib\trees\EditableNodelike;

class Intrinsic extends Expr {
  public string $ident;
  public Type $type;

  public function __construct(string $ident, Type $type) {
    parent::__construct($type);
    $this->ident = $ident;
  }

  public function children(): array {
    return [];
  }

  public function from_children(array $children): EditableNodelike {
    return $this;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('intrinsic')
      ->space()
      ->ident($this->ident)
      ->space()
      ->paren_left()
      ->increase_indentation()
      ->type($this->type)
      ->decrease_indentation()
      ->paren_right()
      ->paren_right();
  }
}
