<?php

namespace Cthulhu\ir\types;

abstract class VariantFields {
  abstract function accepts_constructor(ConstructorFields $fields): bool;

  abstract function bind_parameters(array $replacements): self;

  abstract function __toString(): string;
}
