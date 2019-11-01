<?php

namespace Cthulhu\ir\types;

class RecordType extends Type {
  public $fields;

  function __construct(array $fields) {
    $this->fields = $fields;
  }

  function size(): int {
    return count($this->fields);
  }

  function accepts_as_parameter(Type $other): bool {
    if ($other instanceof self) {
      if ($this->size() === $other->size()) {
        foreach ($this->fields as $name => $type) {
          if (
            !isset($other->fields[$name]) ||
            !$type->accepts_as_parameter($other->fields[$name])
          ) {
            return false;
          }
        }
        return true;
      }
    }
    return false;
  }

  function unify(Type $other): ?Type {
    // TODO: Implement unify() method.
  }

  function __toString(): string {
    if (empty($this->fields)) {
      return '{ }';
    }
    $fields = [];
    foreach ($this->fields as $name => $type) {
      $fields[] = "$name: $type";
    }
    return '{ ' . implode(', ', $fields) . ' }';
  }
}
