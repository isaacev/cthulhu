<?php

namespace Cthulhu\Types;

class VoidType extends Type {
  public function accepts(Type $other): bool {
    if ($other instanceof VoidType) {
      return true;
    } else {
      return false;
    }
  }

  public function __toString(): string {
    return 'Void';
  }

  public function jsonSerialize() {
    return [
      'type' => 'VoidType'
    ];
  }
}
