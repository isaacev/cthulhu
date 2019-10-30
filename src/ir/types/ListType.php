<?php

namespace Cthulhu\ir\types;

class ListType extends Type {
  public $element;

  function __construct(?Type $element = null) {
    $this->element = $element;
  }

  function is_empty(): bool {
    return $this->element === null;
  }

  function accepts_as_parameter(Type $other): bool {
    if ($other instanceof self) {
      if ($this->is_empty()) {
        return $other->is_empty();
      } else if ($other->is_empty()) {
        return true;
      } else {
        return $this->element->accepts_as_parameter($other->element);
      }
    }
    return false;
  }

  function unify(Type $other): ?Type {
    if ($other instanceof self) {
      if ($this->is_empty()) {
        return $other;
      } else if ($other->is_empty()) {
        return $this;
      } else if ($unified = $this->element->unify($other->element)) {
        return new self($unified);
      }
    }
    return null;
  }

  function __toString(): string {
    if ($this->is_empty()) {
      return '[]';
    }
    return "[$this->element]";
  }

  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
