<?php

namespace Cthulhu\ir\nodes;

class FuncItem extends Item {
  public $name;
  public $params;
  public $body;

  function __construct(Name $name, array $params, Note $output, Block $body, array $attrs) {
    parent::__construct($attrs);
    $this->name   = $name;
    $this->params = $params;
    $this->output = $output;
    $this->body   = $body;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->params,
      [
        $this->output,
        $this->body,
      ]
    );
  }
}
