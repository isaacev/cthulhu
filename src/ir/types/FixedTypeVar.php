<?php

namespace Cthulhu\ir\types;

class FixedTypeVar extends Atomic {
  private static int $next_id = 0;

  private int $id;

  public function __construct(string $name) {
    parent::__construct("'$name");
    $this->id = FixedTypeVar::$next_id++;
  }

  public function get_id(): int {
    return $this->id;
  }

  public function __toString(): string {
    return "$this->name";
  }
}
