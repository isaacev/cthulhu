<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Expr $callee, array $args) {
    $this->callee = $callee;
    $this->args = $args;
  }

  public function type(): Type {
    return $this->callee->type()->returns;
  }

  public function jsonSerialize() {
    return [
      'type' => 'CallExpr'
    ];
  }
}
