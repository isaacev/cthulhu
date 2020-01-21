<?php

namespace Cthulhu\types;

abstract class Oper extends Type {
  public string $name;
  public array $types;

  /**
   * @param string $name
   * @param Type[] $types
   */
  public function __construct(string $name, array $types) {
    $this->name  = $name;
    $this->types = $types;
  }

  public function arity(): int {
    return count($this->types);
  }

  public function __toString(): string {
    if (empty($this->types)) {
      return $this->name;
    }
    return "$this->name(" . implode(", ", $this->types) . ")";
  }
}
