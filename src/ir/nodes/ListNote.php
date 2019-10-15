<?php

namespace Cthulhu\ir\nodes;

class ListNote extends Note {
  public $elements;

  function __construct(Note $elements) {
    parent::__construct();
    $this->elements = $elements;
  }

  function children(): array {
    return [ $this->elements ];
  }
}
