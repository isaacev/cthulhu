<?php

namespace Cthulhu\ast\nodes;

class FnParams extends Node {
  public array $params;

  /**
   * @param ParamNode[] $params
   */
  public function __construct(array $params) {
    parent::__construct();
    $this->params = $params;
  }

  public function children(): array {
    return $this->params;
  }
}
