<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class UnaryExpr extends Expr {
  public $operator;
  public $operand;

  function __construct(Source\Span $span, string $operator, Expr $operand) {
    parent::__construct($span);
    $this->operator = $operator;
    $this->operand = $operand;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('UnaryExpr', $visitor_table)) {
      $visitor_table['UnaryExpr']($this);
    }

    $this->operand->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      "type" => "UnaryExpr",
      "operator" => $this->operator,
      "operand" => $this->operand->jsonSerialize()
    ];
  }
}
