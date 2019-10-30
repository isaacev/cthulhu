<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\nodes;

class ParamType extends Type {
  public $name;

  function __construct(nodes\Name $name) {
    $this->name = $name;
  }

  function accepts_as_parameter(Type $other): bool {
    return true;
  }

  function accepts_as_return(Type $other): bool {
    if ($other instanceof self) {
      return $this->name->value === $other->name->value;
    }
    return false;
  }

  function unify(Type $other): ?Type {
    if ($other instanceof self && $this->name->value === $other->name->value) {
      return new self($this->name);
    }
    return null;
  }

  function __toString(): string {
    return "'$this->name";
  }
}
