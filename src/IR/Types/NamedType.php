<?php

namespace Cthulhu\IR\Types;

use Cthulhu\IR;

class NamedType extends Type {
  public $symbol;
  public $hidden;

  function __construct(IR\Symbol $symbol, ?Type $hidden) {
    $this->symbol = $symbol;
    $this->hidden = $hidden;
  }

  function equals(Type $other): bool {
    return (
      $other instanceof NamedType &&
      $this->symbol->equals($other->symbol)
    );
  }

  function __toString(): string {
    return $this->symbol->__toString();
  }
}
