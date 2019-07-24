<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class LetStatement extends Statement {
  public $name;
  public $expression;

  function __construct(Span $span, string $name, Expression $expression) {
    parent::__construct($span);
    $this->name = $name;
    $this->expression = $expression;
  }

  public function jsonSerialize() {
    return [
      'type' => 'LetStatement',
      'name' => $this->name,
      'expression' => $this->expression->jsonSerialize()
    ];
  }
}
