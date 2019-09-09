<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class BoolExpr extends Expr {
  public $value;

  function __construct(bool $value) {
    $this->value = $value;
  }

  public function type(): Types\Type {
    return new Types\BoolType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'BoolExpr',
      'value' => $this->value
    ];
  }
}
