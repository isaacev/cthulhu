<?php

namespace Cthulhu\ir\types\hm;

abstract class Type {
  abstract public function flatten(): Type;

  abstract public function is_unit(): bool;

  abstract public function fresh(callable $fresh_rec): self;

  abstract public function __toString(): string;
}
