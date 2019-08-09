<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class BinaryExpr extends Expr {
  public $operator;
  public $left;
  public $right;

  function __construct(Span $span, string $operator, Expr $left, Expr $right) {
    parent::__construct($span);
    $this->operator = $operator;
    $this->left = $left;
    $this->right = $right;
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
