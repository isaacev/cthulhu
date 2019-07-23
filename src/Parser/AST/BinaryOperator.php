<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class BinaryOperator extends Expression {
  public $operator;
  public $left;
  public $right;

  function __construct(string $operator, Expression $left, Expression $right) {
    $this->operator = $operator;
    $this->left = $left;
    $this->right = $right;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->left->from();
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
