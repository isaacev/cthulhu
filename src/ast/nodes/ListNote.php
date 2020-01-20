<?php

namespace Cthulhu\ast\nodes;

class ListNote extends Note {
  public Note $elements;

  public function __construct(Note $elements) {
    parent::__construct();
    $this->elements = $elements;
  }

  public function children(): array {
    return [ $this->elements ];
  }
}
