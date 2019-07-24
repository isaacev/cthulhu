<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class CallExpression extends Expression {
  public $callee;
  public $arguments;

  function __construct(Span $span, Expression $callee, array $arguments) {
    parent::__construct($span);
    $this->callee = $callee;
    $this->arguments = $arguments;
  }

  public function jsonSerialize() {
    $args = array_map(function ($arg) {
      return $arg->jsonSerialize();
    }, $this->arguments);

    return [
      'type' => 'CallExpression',
      'callee' => $this->callee->jsonSerialize(),
      'arguments' => $args
    ];
  }
}
