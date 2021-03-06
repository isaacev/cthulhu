<?php

namespace Cthulhu\ir\nodes;

class OrderedFormMember extends Node {
  public int $position;
  public Pattern $pattern;

  public function __construct(int $position, Pattern $pattern) {
    parent::__construct();
    $this->position = $position;
    $this->pattern  = $pattern;
  }

  public function children(): array {
    return [ $this->pattern ];
  }

  public function from_children(array $children): OrderedFormMember {
    return new OrderedFormMember($this->position, ...$children);
  }

  public function build(): Builder {
    return $this->pattern->build();
  }

  public function __toString(): string {
    return "$this->pattern";
  }
}
