<?php

namespace Cthulhu\ir\types;

class NamedVariantFields extends VariantFields {
  public $mapping;

  /**
   * NamedVariantFields constructor.
   * @param array[string]Type $mapping
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

  function accepts_constructor(ConstructorFields $fields): bool {
    if ($fields instanceof NamedConstructorFields && $this->size() === $fields->size()) {
      foreach ($this->mapping as $name => $this_type) {
        $other_type = $fields->get($name);
        if ($other_type === null || $this_type->accepts_as_parameter($other_type) === false) {
          return false;
        }
      }
      return true;
    }
    return false;
  }

  function bind_parameters(array $replacements): VariantFields {
    $new_mapping = [];
    foreach ($this->mapping as $field_name => $field_type) {
      $new_mapping[$field_name] = $field_type->bind_parameters($replacements);
    }
    return new self($new_mapping);
  }

  function __toString(): string {
    $out = '';
    foreach ($this->mapping as $name => $type) {
      if ($out === '') {
        $out .= "$name: $type";
      } else {
        $out .= ", $name: $type";
      }
    }
    return " { $out }";
  }
}
