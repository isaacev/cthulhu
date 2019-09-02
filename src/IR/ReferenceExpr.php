<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class ReferenceExpr extends Expr {
  public $symbol;
  public $type;

  function __construct(Symbol $symbol, Types\Type $type) {
    $this->symbol = $symbol;
    $this->type = $type;
  }

  public function type(): Types\Type {
    return $this->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'ReferenceExpr',
      'symbol' => $this->symbol
    ];
  }
}
