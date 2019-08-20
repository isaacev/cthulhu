<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class NumExpr extends Expr {
  public $value;

  function __construct(int $value) {
    $this->value = $value;
  }

  public function type(): Types\Type {
    return new Types\NumType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumExpr',
      'value' => $this->value
    ];
  }
}
