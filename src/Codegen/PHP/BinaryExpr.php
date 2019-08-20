<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

const PREC_COMP = 10;
const PREC_ADD = 20;
const PREC_MULT = 30;

class BinaryExpr extends Expr {
  public $operator;
  public $left;
  public $right;

  function __construct(string $operator, Expr $left, Expr $right) {
    $this->operator = $operator;
    $this->left = $left;
    $this->right = $right;
  }

  public function precedence(): int {
    switch ($this->operator) {
      case '<':
      case '>':
      case '<=':
      case '>=':
        return PREC_COMP;
      case '+':
      case '-':
        return PREC_ADD;
      case '*':
      case '/':
        return PREC_MULT;
      default:
        return PHP_INT_MAX;
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->left, $this->precedence())
      ->operator($this->operator)
      ->expr($this->right, $this->precedence());
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
