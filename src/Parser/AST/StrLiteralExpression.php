<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class StrLiteralExpression extends Expression {
  public $value;
  public $raw;

  function __construct(Span $span, string $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }

  public function jsonSerialize() {
    return [
      'type' => 'StrLiteralExpression',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
