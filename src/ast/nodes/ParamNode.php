<?php

namespace Cthulhu\ast\nodes;

class ParamNode extends Node {
  public LowerName $name;
  public Note $note;

  public function __construct(LowerName $name, Note $note) {
    parent::__construct();
    $this->name = $name;
    $this->note = $note;
  }

  public function children(): array {
    return [ $this->name, $this->note ];
  }
}
