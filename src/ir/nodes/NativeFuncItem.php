<?php

namespace Cthulhu\ir\nodes;

class NativeFuncItem extends Item {
  public $name;
  public $polys;
  public $note;

  function __construct(Name $name, array $polys, FuncNote $note, array $attrs) {
    parent::__construct($attrs);
    $this->name  = $name;
    $this->polys = $polys;
    $this->note  = $note;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->polys,
      [ $this->note ]
    );
  }
}
