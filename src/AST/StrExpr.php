<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class StrExpr extends Expr {
  public $value;
  public $raw;

  function __construct(Source\Span $span, string $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('StrExpr', $visitor_table)) {
      $visitor_table['StrExpr']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'StrExpr',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
