<?php

namespace Cthulhu\ir\types;

class ListType extends Type {
  public $element;

  function __construct(Type $element) {
    $this->element = $element;
  }

  function accepts(Type $other): bool {
    if ($other instanceof self) {
      return $this->element->accepts($other->element);
    }
    return false;
  }

  function replace_generics(array $replacements): Type {
    return new self($this->element->replace_generics($replacements));
  }

  function __toString(): string {
    return "[$this->element]";
  }

  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
