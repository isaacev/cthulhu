<?php

namespace Cthulhu\ir\patterns;

class VariantPattern extends Pattern {
  public string $name;
  public ?VariantFields $fields;

  public function __construct(string $name, ?VariantFields $fields) {
    $this->name   = $name;
    $this->fields = $fields;
  }

  public function __toString(): string {
    if ($this->fields) {
      return (string)$this->name . (string)$this->fields;
    } else {
      return (string)$this->name;
    }
  }
}
