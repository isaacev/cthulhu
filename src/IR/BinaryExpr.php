<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class BinaryExpr extends Expr {
  public $type;
  public $operator;
  public $left;
  public $right;

  function __construct(Type $type, string $operator, Expr $left, Expr $right) {
    $this->type = $type;
    $this->operator = $operator;
    $this->left = $left;
    $this->right = $right;
  }

  public function type(): Type {
    return $this->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'BinaryExpr',
      'operator' => $this->operator,
      'left' => $this->left->jsonSerialize(),
      'right' => $this->right->jsonSerialize()
    ];
  }
}
