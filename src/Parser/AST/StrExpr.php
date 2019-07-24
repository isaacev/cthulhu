<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class StrExpr extends Expr {
  public $value;
  public $raw;

  function __construct(Span $span, string $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }

  public function jsonSerialize() {
    return [
      'type' => 'StrExpr',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
