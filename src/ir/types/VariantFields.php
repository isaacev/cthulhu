<?php

namespace Cthulhu\ir\types;

abstract class VariantFields {
  abstract function accepts_constructor(ConstructorFields $fields): bool;

  abstract function __toString(): string;
}
