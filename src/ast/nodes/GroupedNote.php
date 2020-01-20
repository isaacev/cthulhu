<?php

namespace Cthulhu\ast\nodes;

class GroupedNote extends Note {
  public Note $inner;

  public function __construct(Note $inner) {
    parent::__construct();
    $this->inner = $inner;
  }

  public function children(): array {
    return [ $this->inner ];
  }
}
