<?php

namespace Cthulhu\ir\nodes;

class ListPatternMember extends Node {
  public int $index;
  public Pattern $pattern;

  public function __construct(int $index, Pattern $pattern) {
    parent::__construct();
    $this->index   = $index;
    $this->pattern = $pattern;
  }

  public function children(): array {
    return [ $this->pattern ];
  }

  public function from_children(array $children): ListPatternMember {
    return new ListPatternMember($this->index, ...$children);
  }

  public function build(): Builder {
    return $this->pattern->build();
  }

  public function __toString() {
    return "$this->pattern";
  }
}
