<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

interface Scope {
  public function has_binding(string $name): bool;
  public function get_binding(string $name): Symbol;
}
