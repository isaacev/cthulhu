<?php

namespace Cthulhu\ir\types;

abstract class ConcreteType extends Type {
  public string $name;
  public array $params;

  /**
   * @param string $name
   * @param Type[] $params
   */
  public function __construct(string $name, array $params = []) {
    $this->name   = $name;
    $this->params = $params;
  }

  public function flatten(): Type {
    return $this;
  }

  public function contains(Type $other): bool {
    if ($this === $other) {
      return true;
    }

    foreach ($this->params as $param) {
      if ($param->contains($other)) {
        return true;
      }
    }

    return false;
  }

  public function __toString(): string {
    if (empty($this->params)) {
      return $this->name;
    } else {
      return "$this->name(" . implode(", ", $this->params) . ")";
    }
  }
}
