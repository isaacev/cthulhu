<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class ExpressionStatement extends Statement {
  public $expression;

  function __construct(Expression $expression) {
    $this->expression = $expression;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->expression->from();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ExpressionStatement',
      'expression' => $this->expression->jsonSerialize()
    ];
  }
}
