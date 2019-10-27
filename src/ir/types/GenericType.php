<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\names;

class GenericType extends Type {
  public $name;
  public $symbol;

  function __construct(string $name, names\TypeSymbol $symbol) {
    $this->name   = $name;
    $this->symbol = $symbol;
  }

  function accepts(Type $other): bool {
    return true;
  }

  function unify(Type $other): ?Type {
    if ($this->equals($other)) {
      return $this;
    }
    return null;
  }

  function equals(Type $other): bool {
    if ($other instanceof self) {
      return (
        $this->name === $other->name &&
        $other->symbol->equals($other->symbol)
      );
    }
    return false;
  }

  function replace_generics(array $replacements): Type {
    if (array_key_exists($this->symbol->get_id(), $replacements)) {
      return $replacements[$this->symbol->get_id()];
    } else {
      return $this;
    }
  }

  function __toString(): string {
    return $this->name;
  }
}
