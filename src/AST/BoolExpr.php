<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class BoolExpr extends Expr {
  public $value;
  public $raw;

  function __construct(Source\Span $span, bool $value, string $raw) {
    parent::__construct($span);
    $this->value = $value;
    $this->raw = $raw;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('BoolExpr', $visitor_table)) {
      $visitor_table['BoolExpr']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'BoolExpr',
      'value' => $this->value,
      'raw' => $this->raw
    ];
  }
}
