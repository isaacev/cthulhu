<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

const PREC_UNARY = 40;

class UnaryExpr extends Expr {
  public $operator;
  public $operand;

  function __construct(string $operator, Expr $operand) {
    $this->operator = $operator;
    $this->operand = $operand;
  }

  public function to_children(): array {
    return [ $this->operand ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->operator, $nodes[0]);
  }

  public function precedence(): int {
    switch ($this->operator) {
      case '-':
        return PREC_UNARY;
      default:
        return PHP_INT_MAX;
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->operator($this->operator)
      ->expr($this->operand, $this->precedence());
  }
}
