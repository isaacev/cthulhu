<?php

namespace Cthulhu\ir\types\hm;

class Unit extends Nullary {
  public function __construct() {
    parent::__construct('Unit');
  }

  public function is_unit(): bool {
    return true;
  }

  public function fresh(callable $fresh_rec): Type {
    return $this;
  }
}
