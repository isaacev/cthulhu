<?php

namespace Cthulhu\ir\types;

class NamedConstructorFields extends ConstructorFields {
  public array $mapping;

  /**
   * @param Type[] $mapping
   */
  function __construct(array $mapping) {
    $this->mapping = $mapping;
  }

  function size(): int {
    return count($this->mapping);
  }

  function get(string $name): ?Type {
    return isset($this->mapping[$name])
      ? $this->mapping[$name]
      : null;
  }

  function __toString(): string {
    $out = '';
    foreach ($this->mapping as $name => $type) {
      if (empty($out)) {
        $out .= " $name: $type";
      } else {
        $out .= ", $name: $type";
      }
    }
    return '{' . $out . ' }';
  }
}
