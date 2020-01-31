<?php

namespace Cthulhu\ir\patterns;

class NamedFormFields extends FormFields {
  public array $mapping;

  /**
   * @param Pattern[] $mapping
   */
  public function __construct(array $mapping) {
    $this->mapping = $mapping;
  }

  public function __toString(): string {
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
