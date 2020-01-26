<?php

namespace Cthulhu\php;

class ExpressionStack {
  /* @var nodes\Expr[] $stack */
  private array $stack = [];

  private array $stashes = [];

  public function push(nodes\Expr $expr): void {
    array_push($this->stack, $expr);
  }

  public function pop(): nodes\Expr {
    if (empty($this->stack)) {
      die("tried to pop an expression from an empty stack\n");
    }
    return array_pop($this->stack);
  }

  /**
   * @param int $n
   * @return nodes\Expr[]
   */
  public function pop_multiple(int $n): array {
    assert($n >= 0);
    if ($n === 0) {
      return [];
    } else if (count($this->stack) < $n) {
      die("tried to pop too many expressions from the stack\n");
    } else {
      return array_splice($this->stack, -$n);
    }
  }

  public function current_stack_depth(): int {
    return count($this->stack);
  }

  public function store_stack_depth(): void {
    array_push($this->stashes, count($this->stack));
  }

  public function remember_stack_depth(): int {
    return array_pop($this->stashes);
  }
}
