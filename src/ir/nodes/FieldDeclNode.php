<?php

namespace Cthulhu\ir\nodes;

class FieldDeclNode extends Node {
  public Name $name;
  public Note $note;

  public function __construct(Name $name, Note $note) {
    parent::__construct();
    $this->name = $name;
    $this->note = $note;
  }

  public function children(): array {
    return [
      $this->name,
      $this->note,
    ];
  }
}
