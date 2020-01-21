<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\trees\EditableNodelike;

class NameExpr extends Expr {
  public Name $name;

  public function __construct(Name $name) {
    parent::__construct($name->type);
    $this->name = $name;
  }

  public function children(): array {
    return [ $this->name ];
  }

  public function from_children(array $children): EditableNodelike {
    return (new self($children[0]))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->then($this->name);
  }
}
