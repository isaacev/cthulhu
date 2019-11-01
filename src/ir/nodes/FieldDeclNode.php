<?php

namespace Cthulhu\ir\nodes;

class FieldDeclNode extends Node {
  public $name;
  public $note;

  function __construct(Name $name, Note $note) {
    parent::__construct();
    $this->name = $name;
    $this->note = $note;
  }

  function children(): array {
    return [
      $this->name,
      $this->note,
    ];
  }
}
