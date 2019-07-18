<?php

namespace Cthulhu\Parser\AST;

class CallExpression extends Expression {
  public $callee;
  public $arguments;

  function __construct(Expression $callee, array $arguments) {
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
