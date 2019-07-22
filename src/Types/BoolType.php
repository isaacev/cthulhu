<?php

namespace Cthulhu\Types;

class BoolType extends Type {
  public function accepts(Type $other): bool {
    if ($other instanceof BoolType) {
      return true;
    } else {
      return false;
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'BoolType'
    ];
  }
}
