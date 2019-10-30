<?php

namespace Cthulhu\ir\types;

class TupleType extends Type {
  public $members;

  function __construct(array $members) {
    assert(count($members) > 1);
    $this->members = $members;
  }

  function size(): int {
    return count($this->members);
  }

  function accepts_as_parameter(Type $other): bool {
    if ($other instanceof self) {
      if ($this->size() === $other->size()) {
        for ($i = 0; $i < $this->size(); $i++) {
          if ($this->members[$i]->accepts($other->members[$i]) === false) {
            return false;
          }
        }
        return true;
      }
    }
    return false;
  }

  function unify(Type $other): ?Type {
    if ($other instanceof self) {
      if ($this->size() === $other->size()) {
        $new_members = [];
        for ($i = 0; $i < $this->size(); $i++) {
          if ($unified = $this->members[$i]->unify($other->members[$i])) {
            $new_members[] = $unified;
          } else {
            return null;
          }
        }
        return new self($new_members);
      }
    }
    return null;
  }

  function __toString(): string {
    return '(' . implode(',', $this->members) . ')';
  }
}
