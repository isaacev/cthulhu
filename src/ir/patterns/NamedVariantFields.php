<?php

namespace Cthulhu\ir\patterns;

class NamedVariantFields extends VariantFields {
  public array $mapping;

  /**
   * @param Pattern[] $mapping
   */
  function __construct(array $mapping) {
    $this->mapping = $mapping;
  }

  function __toString(): string {
    $out = '';
    foreach ($this->mapping as $name => $pattern) {
      if (empty($out)) {
        $out .= " $name: $pattern";
      } else {
        $out .= ", $name: $pattern";
      }
    }
    return ' {' . $out . ' }';
  }
}
