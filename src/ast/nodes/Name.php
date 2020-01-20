<?php

namespace Cthulhu\ast\nodes;

abstract class Name extends Node {
  public string $value;

  public function __construct(string $value) {
    parent::__construct();
    $this->value = $value;
  }

  public function children(): array {
    return [];
  }

  public function __toString(): string {
    return $this->value;
  }
}
