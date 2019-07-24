<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class NumLiteralExpression extends Expression {
  public $value;
  public $raw;

  function __construct(Span $span, int $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumLiteralExpression',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
