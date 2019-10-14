<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class IntExpr extends Expr {
  public $value;
  public $raw;

  function __construct(Source\Span $span, int $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('IntExpr', $visitor_table)) {
      $visitor_table['IntExpr']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'IntExpr',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
