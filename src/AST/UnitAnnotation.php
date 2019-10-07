<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class UnitAnnotation extends Annotation {
  public function visit(array $visitor_table): void {
    if (array_key_exists('UnitAnnotation', $visitor_table)) {
      $visitor_table['UnitAnnotation']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'UnitAnnotation'
    ];
  }
}
