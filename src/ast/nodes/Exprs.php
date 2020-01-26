<?php

namespace Cthulhu\ast\nodes;

use Countable;

class Exprs extends Node implements Countable {
  public array $exprs;

  public function __construct(array $exprs) {
    parent::__construct();
    $this->exprs = $exprs;
  }

  public function count() {
    return count($this->exprs);
  }

  public function children(): array {
    return $this->exprs;
  }
}
