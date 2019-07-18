<?php

namespace Cthulhu\Parser\AST;

class LetStatement extends Statement {
  public $name;
  public $expression;

  function __construct(string $name, Expression $expression) {
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
