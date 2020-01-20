<?php

namespace Cthulhu\ast\nodes;

class FuncNote extends Note {
  public Note $input;
  public Note $output;

  public function __construct(Note $input, Note $output) {
    parent::__construct();
    $this->input  = $input;
    $this->output = $output;
  }

  public function children(): array {
    return [
      $this->input,
      $this->output,
    ];
  }
}
