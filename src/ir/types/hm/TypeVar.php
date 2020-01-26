<?php

namespace Cthulhu\ir\types\hm;

class TypeVar extends Type {
  private static int $next_id = 0;

  public int $id;
  public ?Type $instance;

  public function is_unit(): bool {
    if ($this->instance) {
      return $this->instance->is_unit();
    } else {
      return false;
    }
  }

  public function __construct(?Type $instance = null) {
    $this->id       = self::$next_id++;
    $this->instance = $instance;
  }

  public function flatten(): Type {
    if ($this->instance === null) {
      return $this;
    } else {
      return $this->instance->flatten();
    }
  }

  public function fresh($fresh_rec): Type {
    return $fresh_rec($this);
  }

  public function __toString(): string {
    if ($this->instance) {
      return "$this->instance";
    }
    return "'$this->id"; // TODO: better var name assignment
  }
}
