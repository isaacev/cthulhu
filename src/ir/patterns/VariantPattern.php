<?php

namespace Cthulhu\ir\patterns;

class VariantPattern extends Pattern {
  public $name;
  public $fields;

  function __construct(string $name, ?VariantFields $fields) {
    $this->name   = $name;
    $this->fields = $fields;
  }

  function __toString(): string {
    if ($this->fields) {
      return (string)$this->name . (string)$this->fields;
    } else {
      return (string)$this->name;
    }
  }
}
