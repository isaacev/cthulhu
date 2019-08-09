<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Span $span, Expr $callee, array $args) {
    parent::__construct($span);
    $this->callee = $callee;
    $this->args = $args;
  }

  public function jsonSerialize() {
    $args = array_map(function ($arg) {
      return $arg->jsonSerialize();
    }, $this->args);

    return [
      'type' => 'CallExpr',
      'callee' => $this->callee->jsonSerialize(),
      'args' => $args
    ];
  }
}
