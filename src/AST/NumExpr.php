<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class NumExpr extends Expr {
  public $value;
  public $raw;

  function __construct(Span $span, int $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumExpr',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
