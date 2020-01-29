<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\names\Symbol;

class FreeTypeVar extends Type {
  private static int $next_id = 0;

  private int $id;
  private string $name;
  private Symbol $symbol;
  private ?Type $instance;

  public function __construct(string $name, Symbol $symbol, ?Type $instance = null) {
    $this->id       = FreeTypeVar::$next_id++;
    $this->name     = $name;
    $this->symbol   = $symbol;
    $this->instance = $instance;
  }

  public function set_instance(Type $new_instance): void {
    $this->instance = $new_instance;
  }

  public function flatten(): Type {
    if ($this->instance) {
      return $this->instance->flatten();
    }
    return $this;
  }

  public function contains(Type $other): bool {
    if ($this === $other) {
      return true;
    } else if ($this->instance) {
      return $this->instance->contains($other);
    } else {
      return false;
    }
  }

  public function prune(): Type {
    if ($this->instance) {
      $this->instance = $this->instance->prune();
      return $this->instance;
    }
    return $this;
  }

  public function fresh(ParameterContext $ctx): Type {
    if ($new_var = $ctx->read($this->symbol)) {
      return $new_var;
    }

    $new_var = new FreeTypeVar($this->name, $this->symbol, null);
    $ctx->write($this->symbol, $new_var);
    return $new_var;
  }

  public function __toString(): string {
    if ($this->instance) {
      return "$this->instance";
    } else {
      return "'$this->name";
    }
  }
}
