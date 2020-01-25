<?php

namespace Cthulhu\ast\nodes;

class UpperName extends Name {
  public const VALID_UPPER_NAME_PATTERN = '/^[A-Z][A-Za-z0-9_]*$/';

  public string $value;

  public function __construct(string $value) {
    assert(preg_match(self::VALID_UPPER_NAME_PATTERN, $value));
    parent::__construct($value);
  }
}
