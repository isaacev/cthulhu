<?php

namespace Cthulhu\ir\types\hm;

class TypeOper extends Type {
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

  public function flatten(): Type {
    return $this;
  }

  public function is_unit(): bool {
    return false;
  }

  public function fresh(callable $fresh_rec): Type {
    return new self(
      $this->name,
      array_map(fn(Type $t) => $t->fresh($fresh_rec), $this->types),
    );
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
