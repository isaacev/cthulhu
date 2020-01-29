<?php

namespace Cthulhu\ast\nodes;

class Operator extends Node implements FnName {
  public int $precedence;
  public bool $is_right_assoc;
  public string $value;
  public int $min_arity;

  public function __construct(int $precedence, bool $is_right_assoc, string $value, int $min_arity) {
    parent::__construct();
    $this->precedence     = $precedence;
    $this->is_right_assoc = $is_right_assoc;
    $this->value          = $value;
    $this->min_arity      = $min_arity;
  }

  public function duplicate(): Operator {
    return new Operator($this->precedence, $this->is_right_assoc, $this->value, $this->min_arity);
  }

  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return $this->value;
  }
}
