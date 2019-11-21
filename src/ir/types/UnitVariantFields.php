<?php

namespace Cthulhu\ir\types;

class UnitVariantFields extends VariantFields {
  function accepts_constructor(ConstructorFields $fields): bool {
    return $fields instanceof UnitConstructorFields;
  }

  function __toString(): string {
    return '';
  }
}
