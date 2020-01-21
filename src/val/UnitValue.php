<?php

namespace Cthulhu\val;

class UnitValue extends Value {
  public function encode_as_php(): string {
    return 'NULL';
  }
}
