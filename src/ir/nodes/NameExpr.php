<?php

namespace Cthulhu\ir\nodes;

class NameExpr extends Expr {
  public Name $name;

  public function __construct(Name $name) {
    parent::__construct($name->type);
    $this->name = $name;
  }

  public function children(): array {
    return [ $this->name ];
  }

  public function from_children(array $children): NameExpr {
    return (new NameExpr($children[0]))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->then($this->name);
  }
}
