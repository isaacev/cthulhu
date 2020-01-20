<?php

namespace Cthulhu\ast\nodes;

class LowerName extends Name implements FnName {
  public function __construct(string $value) {
    assert($value[0] >= 'a' && $value[0] <= 'z');
    parent::__construct($value);
  }
}
