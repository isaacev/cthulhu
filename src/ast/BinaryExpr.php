<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class BinaryExpr extends Expr {
  public $operator;
  public $left;
  public $right;

  function __construct(Source\Span $span, string $operator, Expr $left, Expr $right) {
    parent::__construct($span);
    $this->operator = $operator;
    $this->left = $left;
    $this->right = $right;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('BinaryExpr', $visitor_table)) {
      $visitor_table['BinaryExpr']($this);
    }

    $this->left->visit($visitor_table);
    $this->right->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      "type" => "BinaryExpr",
      "operator" => $this->operator,
      "left" => $this->left->jsonSerialize(),
      "right" => $this->right->jsonSerialize()
    ];
  }
}
