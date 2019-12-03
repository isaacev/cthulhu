<?php

namespace Cthulhu\ir\nodes;

class FuncHead extends Node {
  public Name $name;
  public array $params;
  public Note $output;

  /**
   * @param Name        $name
   * @param FuncParam[] $params
   * @param Note        $output
   */
  function __construct(Name $name, array $params, Note $output) {
    parent::__construct();
    $this->name   = $name;
    $this->params = $params;
    $this->output = $output;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->params,
      [ $this->output ]
    );
  }
}
