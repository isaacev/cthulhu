<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;
use Cthulhu\val\Value;

abstract class Lit extends Expr {
  public Value $value;

  public function __construct(Type $type, Value $value) {
    parent::__construct($type);
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  public function from_children(array $children): Lit {
    return ($this)
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->value($this->value)
      ->colon()
      ->type($this->type);
  }
}
