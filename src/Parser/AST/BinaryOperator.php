<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class BinaryOperator extends Expression {
  public $operator;
  public $left;
  public $right;

  function __construct(Span $span, string $operator, Expression $left, Expression $right) {
    parent::__construct($span);
    $this->operator = $operator;
    $this->left = $left;
    $this->right = $right;
  }

  public function jsonSerialize() {
    return [
      "type" => "BinaryOperator",
      "operator" => $this->operator,
      "left" => $this->left->jsonSerialize(),
      "right" => $this->right->jsonSerialize()
    ];
  }
}
