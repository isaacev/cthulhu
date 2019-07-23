<?php

namespace Cthulhu\Types;

class NumType extends Type {
  public function accepts(Type $other): bool {
    if ($other instanceof NumType) {
      return true;
    } else {
      return false;
    }
  }

  public function __toString(): string {
    return 'Num';
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumType'
    ];
  }
}
