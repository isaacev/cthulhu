<?php

namespace Cthulhu\ir\nodes;

class Name extends Node {
  public string $value;

  function __construct(string $value) {
    parent::__construct();
    $this->value = $value;
  }

  function children(): array {
    return [];
  }

  function __toString(): string {
    return $this->value;
  }
}
