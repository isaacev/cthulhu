<?php

namespace Cthulhu\ir\nodes;

class FuncNote extends Note {
  public $inputs;
  public $output;

  function __construct(array $inputs, Note $output) {
    parent::__construct();
    $this->inputs = $inputs;
    $this->output = $output;
  }

  function children(): array {
    return array_merge(
      $this->inputs,
      [ $this->output ]
    );
  }
}
