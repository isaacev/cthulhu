<?php

namespace Cthulhu\Parser\AST;

class StrLiteralExpression extends Expression {
  public $value;
  public $raw;

  function __construct(string $value, string $raw) {
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
