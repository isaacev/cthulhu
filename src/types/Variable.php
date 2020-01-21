<?php

namespace Cthulhu\types;

class Variable extends Type {
  private static int $next_id = 0;

  public int $id;
  public ?Type $instance;

  public function __construct(?Type $instance = null) {
    $this->id       = self::$next_id++;
    $this->instance = $instance;
  }

  public function __toString(): string {
    if ($this->instance) {
      return "$this->instance";
    }
    return "'$this->id";
  }
}
