<?php

namespace Cthulhu\ast\nodes;

class Glob extends Pattern {
  public ?VariablePattern $binding;

  public function __construct(?VariablePattern $binding) {
    parent::__construct();
    $this->binding = $binding;
  }

  public function children(): array {
    return [ $this->binding ];
  }

  public function __toString(): string {
    if ($this->binding) {
      return "...$this->binding";
    } else {
      return '...';
    }
  }
}
