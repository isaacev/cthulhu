<?php

namespace Cthulhu\ir\types;

use Cthulhu\lib\debug\Debug;

class FreeTypeVar extends Type {
  private static int $next_id = 0;

  private int $id;
  private string $name;
  private ?Type $instance;

  public function __construct(string $name, ?Type $instance) {
    $this->id       = FreeTypeVar::$next_id++;
    $this->name     = $name;
    $this->instance = $instance;
  }

  public function has_instance(): bool {
    return $this->instance !== null;
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
    if ($new_var = $ctx->read($this->id)) {
      return $new_var;
    }

    $new_inst = $this->instance ? $this->instance->fresh($ctx) : null;
    $new_var  = new FreeTypeVar($this->name, $new_inst);
    $ctx->write($this->id, $new_var);
    return $new_var;
  }

  public function __toString(): string {
    if (Debug::is_true()) {
      if ($this->instance) {
        return "<free '$this->name $this->instance>";
      } else {
        return "<free '$this->name>";
      }
    }

    if ($this->instance) {
      return "$this->instance";
    } else {
      return "'$this->name";
    }
  }
}
