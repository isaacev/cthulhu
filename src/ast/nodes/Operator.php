<?php

namespace Cthulhu\ast\nodes;

class Operator extends Node implements FnName {
  public int $precedence;
  public bool $is_right_assoc;
  public string $value;

  public function __construct(int $precedence, bool $is_right_assoc, string $value) {
    parent::__construct();
    $this->precedence     = $precedence;
    $this->is_right_assoc = $is_right_assoc;
    $this->value          = $value;
  }

  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return $this->value;
  }
}
