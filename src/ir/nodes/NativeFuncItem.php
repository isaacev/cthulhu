<?php

namespace Cthulhu\ir\nodes;

class NativeFuncItem extends Item {
  public Name $name;
  public FuncNote $note;

  function __construct(Name $name, FuncNote $note, array $attrs) {
    parent::__construct($attrs);
    $this->name  = $name;
    $this->note  = $note;
  }

  function children(): array {
    return [
      $this->name,
      $this->note,
    ];
  }
}
