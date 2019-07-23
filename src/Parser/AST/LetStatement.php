<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class LetStatement extends Statement {
  public $from;
  public $name;
  public $expression;

  function __construct(Point $from, string $name, Expression $expression) {
    $this->from = $from;
    $this->name = $name;
    $this->expression = $expression;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->from;
  }

  public function jsonSerialize() {
    return [
      'type' => 'LetStatement',
      'name' => $this->name,
      'expression' => $this->expression->jsonSerialize()
    ];
  }
}
