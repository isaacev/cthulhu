<?php

namespace Cthulhu\ir\nodes;

class FuncHead extends Node {
  public $name;
  public $polys;
  public $params;
  public $output;

  function __construct(Name $name, array $polys, array $params, Note $output) {
    parent::__construct();
    $this->name   = $name;
    $this->polys  = $polys;
    $this->params = $params;
    $this->output = $output;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->polys,
      $this->params,
      [ $this->output ]
    );
  }
}
