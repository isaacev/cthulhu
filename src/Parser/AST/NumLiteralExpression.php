<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class NumLiteralExpression extends Expression {
  public $from;
  public $value;
  public $raw;

  function __construct(Point $from, int $value, string $raw) {
    $this->from = $from;
    $this->value = $value;
    $this->raw = $raw;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->from;
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumLiteralExpression',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
