<?php

namespace Cthulhu\ir\nodes;

class Arms extends Node {
  public array $arms;

  /**
   * @param Arm[] $arms
   */
  public function __construct(array $arms) {
    parent::__construct();
    $this->arms = $arms;
  }

  public function children(): array {
    return $this->arms;
  }

  public function from_children(array $children): Arms {
    return new Arms($children);
  }

  public function build(): Builder {
    return (new Builder)
      ->indent()
      ->each($this->arms, (new Builder)
        ->newline()
        ->indent());
  }
}
