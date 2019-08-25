<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

interface MutableScope extends Scope {
  public function new_binding(string $name, Type $type): Symbol;
}
