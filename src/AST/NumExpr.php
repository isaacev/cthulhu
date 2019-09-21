<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class NumExpr extends Expr {
  public $value;
  public $raw;

  function __construct(Source\Span $span, int $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('NumExpr', $visitor_table)) {
      $visitor_table['NumExpr']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumExpr',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
