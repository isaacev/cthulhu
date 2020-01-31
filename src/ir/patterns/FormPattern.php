<?php

namespace Cthulhu\ir\patterns;

class FormPattern extends Pattern {
  public string $name;
  public ?FormFields $fields;

  public function __construct(string $name, ?FormFields $fields) {
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
