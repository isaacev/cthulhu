<?php

namespace Cthulhu\IR;

class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Expr $callee, array $args) {
    $this->callee = $callee;
    $this->args = $args;
  }

  public function jsonSerialize() {
    return [
      'type' => 'CallExpr',
      'callee' => $this->callee->jsonSerialize(),
      'args' => array_map(function ($arg) {
        return $arg->jsonSerialize();
      }, $this->args)
    ];
  }
}
