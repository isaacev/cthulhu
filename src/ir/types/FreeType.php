<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\names\TypeSymbol;
use Cthulhu\ir\nodes\Name;

class FreeType extends Type {
  use traits\NoChildren;
  use traits\DefaultWalkable;

  public TypeSymbol $symbol;
  public Name $name;

  function __construct(TypeSymbol $symbol, Name $name) {
    $this->symbol = $symbol;
    $this->name   = $name;
  }

  function similar_to(Walkable $other): bool {
    return $other instanceof FreeType;
  }

  function equals(Type $other): bool {
    return (
      $other instanceof self &&
      $this->symbol->equals($other->symbol)
    );
  }

  public function __toString(): string {
    return "'$this->name";
  }
}
