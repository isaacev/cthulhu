<?php

namespace Cthulhu\ast\nodes;

class UpperName extends Name {
  public string $value;

  public function __construct(string $value) {
    assert($value[0] >= 'A' && $value[0] <= 'Z');
    parent::__construct($value);
  }
}
