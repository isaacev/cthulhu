<?php

namespace Cthulhu\ir\types\hm;

class TypeMap {
  private array $mapping = [];

  public function has(TypeVar $v): bool {
    return array_key_exists($v->id, $this->mapping);
  }

  public function read(TypeVar $v): ?TypeVar {
    if ($this->has($v)) {
      return $this->mapping[$v->id];
    }
    return null;
  }

  public function write(TypeVar $from, TypeVar $to): void {
    $this->mapping[$from->id] = $to;
  }
}
