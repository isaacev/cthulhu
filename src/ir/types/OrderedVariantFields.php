<?php

namespace Cthulhu\ir\types;

class OrderedVariantFields extends VariantFields {
  public $order;

  /**
   * @param Type[] $order
   */
  function __construct(array $order) {
    $this->order = $order;
  }

  function size(): int {
    return count($this->order);
}

  function get(int $i): ?Type {
    return isset($this->order[$i])
      ? $this->order[$i]
      : null;
  }

  function accepts_constructor(ConstructorFields $fields): bool {
    if ($fields instanceof OrderedConstructorFields && $this->size() === $fields->size()) {
      for ($i = 0; $i < $this->size(); $i++) {
        if ($this->get($i)->accepts_as_parameter($fields->get($i)) === false) {
          return false;
        }
      }
      return true;
    }
    return false;
  }

  function bind_parameters(array $replacements): VariantFields {
    $new_order = [];
    foreach ($this->order as $index => $field_type) {
      $new_order[$index] = $field_type->bind_parameters($replacements);
    }
    return new self($new_order);
  }

  function __toString(): string {
    return '(' . implode(', ', $this->order) . ')';
  }
}
