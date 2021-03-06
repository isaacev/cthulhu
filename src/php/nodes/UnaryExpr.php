<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\panic\Panic;
use Cthulhu\php\Builder;

class UnaryExpr extends Expr {
  public string $operator;
  public Expr $operand;

  public function __construct(string $operator, Expr $operand) {
    parent::__construct();
    $this->operator = $operator;
    $this->operand  = $operand;
  }

  public function children(): array {
    return [ $this->operand ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->operator, $nodes[0]);
  }

  public function precedence(): int {
    switch ($this->operator) {
      case '-':
        return Precedence::SUM;
      case '...':
        return Precedence::ARGUMENT_UNPACK;
      case '!':
        return Precedence::UNARY_NOT;
      default:
        Panic::with_reason(__LINE__, __FILE__, "unknown precedence for `$this->operator` operator");
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->operator($this->operator)
      ->expr($this->operand, $this->precedence());
  }
}
