<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

const PREC_COMP = 10;
const PREC_ADD = 20;
const PREC_MULT = 30;
const PREC_EXP = 40;

class BinaryExpr extends Expr {
  public $operator;
  public $left;
  public $right;

  function __construct(string $operator, Expr $left, Expr $right) {
    parent::__construct();
    $this->operator = $operator;
    $this->left = $left;
    $this->right = $right;
  }

  public function to_children(): array {
    return [ $this->left, $this->right ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->operator, $nodes[0], $nodes[1]);
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
      case '.':
        return PREC_ADD;
      case '*':
      case '/':
        return PREC_MULT;
      case '**':
        return PREC_EXP;
      default:
        return PHP_INT_MAX;
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->left, $this->precedence())
      ->space()
      ->operator($this->operator)
      ->space()
      ->expr($this->right, $this->precedence());
  }
}
