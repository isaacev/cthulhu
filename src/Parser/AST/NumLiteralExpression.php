<?php

namespace Cthulhu\Parser\AST;

class NumLiteralExpression extends Expression {
  public $value;
  public $raw;

  function __construct(int $value, string $raw) {
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
