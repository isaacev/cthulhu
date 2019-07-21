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

  public function jsonSerialize() {
    return [
      'type' => 'StrType'
    ];
  }
}
