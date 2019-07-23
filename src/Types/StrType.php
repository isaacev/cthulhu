<?php

namespace Cthulhu\Types;

class StrType extends Type {
  public function accepts(Type $other): bool {
    if ($other instanceof StrType) {
      return true;
    } else {
      return false;
    }
  }

  public function __toString(): string {
    return 'Str';
  }

  public function jsonSerialize() {
    return [
      'type' => 'StrType'
    ];
  }
}
