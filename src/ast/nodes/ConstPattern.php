<?php

namespace Cthulhu\ast\nodes;

class ConstPattern extends Pattern {
  public Literal $literal;

  public function __construct(Literal $literal) {
    parent::__construct();
    $this->literal = $literal;
  }

  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return $this->literal->value->encode_as_php();
  }
}
