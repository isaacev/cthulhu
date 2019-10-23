<?php

namespace Cthulhu\ir\types;

class UnionType extends Type {
  public $members;

  function __construct(array $members) {
    $this->members = $members;
  }

  function accepts(Type $other): bool {
    if ($other instanceof self) {
      foreach ($other->members as $other_member) {
        if ($this->accepts($other_member) === false) {
          return false;
        }
      }
      return true;
    }
    foreach ($this->members as $member) {
      if ($member->accepts($other)) {
        return true;
      }
    }
    return false;
  }

  function equals(Type $other): bool {
    return $this->accepts($other); // FIXME
  }

  function replace_generics(array $replacements): Type {
    $new_members = [];
    foreach ($this->members as $member) {
      $new_members[] = $member->replace_generics($replacements);
    }
    return new self($new_members);
  }

  function __toString(): string {
    return implode(' | ', $this->members);
  }
}
