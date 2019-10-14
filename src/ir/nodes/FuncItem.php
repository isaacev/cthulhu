<?php

namespace Cthulhu\ir\nodes;

class FuncItem extends Item {
  public $name;
  public $params;
  public $body;

  function __construct(Name $name, array $polys, array $params, Note $output, Block $body, array $attrs) {
    parent::__construct($attrs);
    $this->name   = $name;
    $this->polys  = $polys;
    $this->params = $params;
    $this->output = $output;
    $this->body   = $body;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->polys,
      $this->params,
      [
        $this->output,
        $this->body,
      ]
    );
  }
}
