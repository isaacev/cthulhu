<?php

namespace Cthulhu\IR\Types;

use Cthulhu\IR;

class ParameterType extends Type {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }

  function equals(Type $other): bool {
    return true;
  }

  function __toString(): string {
    return "'$this->name";
  }
}
