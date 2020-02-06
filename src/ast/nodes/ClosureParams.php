<?php

namespace Cthulhu\ast\nodes;

use Countable;

class ClosureParams extends Node implements Countable {
  public array $params;

  /**
   * @param LowerName[] $params
   */
  public function __construct(array $params) {
    parent::__construct();
    $this->params = $params;
  }

  public function count() {
    return count($this->params);
  }

  public function children(): array {
    return $this->params;
  }
}
