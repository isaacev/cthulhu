<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class UnaryExpr extends Expr {
  public $operator;
  public $operand;

  function __construct(string $operator, Expr $operand) {
    parent::__construct();
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
        return Precedence::SUM;
      default:
        throw new \Exception("unknown precedence for `$this->operator` operator");
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->operator($this->operator)
      ->expr($this->operand, $this->precedence());
  }
}
