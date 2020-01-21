<?php

namespace Cthulhu\types;

class Unit extends Nullary {
  public function __construct() {
    parent::__construct('Unit');
  }

  public function __toString(): string {
    return '()';
  }

  public static function make(): self {
    return new Unit();
  }
}
