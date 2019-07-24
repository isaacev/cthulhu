<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class ExpressionStatement extends Statement {
  public $expression;

  function __construct(Span $span, Expression $expression) {
    parent::__construct($span);
    $this->expression = $expression;
  }

  public function jsonSerialize() {
    return [
      'type' => 'ExpressionStatement',
      'expression' => $this->expression->jsonSerialize()
    ];
  }
}
