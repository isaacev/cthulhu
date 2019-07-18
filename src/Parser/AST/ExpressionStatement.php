<?php

namespace Cthulhu\Parser\AST;

class ExpressionStatement extends Statement {
  public $expression;

  function __construct(Expression $expression) {
    $this->expression = $expression;
  }

  public function jsonSerialize() {
    return [
      'type' => 'ExpressionStatement',
      'expression' => $this->expression->jsonSerialize()
    ];
  }
}
