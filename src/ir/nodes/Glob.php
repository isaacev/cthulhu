<?php

namespace Cthulhu\ir\nodes;

class Glob extends Node {
  public int $offset;
  public ?VariablePattern $binding;

  public function __construct(int $offset, ?VariablePattern $binding) {
    parent::__construct();
    $this->offset  = $offset;
    $this->binding = $binding;
  }

  public function children(): array {
    return [ $this->binding ];
  }

  public function from_children(array $children): Glob {
    return new Glob($this->offset, ...$children);
  }

  public function build(): Builder {
    return (new Builder);
  }

  public function __toString() {
    if ($this->binding) {
      return "...$this->binding";
    } else {
      return '...';
    }
  }
}
