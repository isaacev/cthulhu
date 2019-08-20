<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class StrExpr extends Expr {
  public $value;

  function __construct(string $value) {
    $this->value = $value;
  }

  public function type(): Types\Type {
    return new Types\StrType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'StrExpr',
      'value' => $this->value
    ];
  }
}
