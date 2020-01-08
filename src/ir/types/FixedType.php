<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\names\TypeSymbol;
use Cthulhu\ir\nodes\Name;

class FixedType extends Type {
  use traits\NoChildren;
  use traits\DefaultWalkable;

  public TypeSymbol $symbol;
  public Name $name;

  public function __construct(TypeSymbol $symbol, Name $name) {
    $this->symbol = $symbol;
    $this->name   = $name;
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  public function equals(Type $other): bool {
    return (
      $other instanceof self &&
      $this->symbol->equals($other->symbol)
    );
  }

  public function __toString(): string {
    return "'$this->name";
  }
}
