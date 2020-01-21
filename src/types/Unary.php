<?php

namespace Cthulhu\types;

class Unary extends Oper {
  public function __construct(string $name, Type $type) {
    parent::__construct($name, [ $type ]);
  }
}
