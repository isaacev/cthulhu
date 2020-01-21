<?php

namespace Cthulhu\ir\types\hm;

abstract class Type {
  abstract public function fresh(callable $fresh_rec): self;

  abstract public function __toString(): string;
}
