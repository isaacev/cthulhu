<?php

namespace Cthulhu\types;

class Nullary extends Oper {
  public function __construct(string $name) {
    parent::__construct($name, []);
  }
}
