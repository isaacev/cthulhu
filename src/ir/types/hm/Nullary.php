<?php

namespace Cthulhu\ir\types\hm;

class Nullary extends TypeOper {
  public function __construct(string $name) {
    parent::__construct($name, []);
  }

  public function fresh(callable $fresh_rec): Type {
    return new self($this->name);
  }
}
