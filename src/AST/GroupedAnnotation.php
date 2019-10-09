<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class GroupedAnnotation extends Annotation {
  public $inner;

  function __construct(Source\Span $span, Annotation $inner) {
    parent::__construct($span);
    $this->inner = $inner;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('GroupedAnnotation', $visitor_table)) {
      $visitor_table['GroupedAnnotation']($this);
    }

    $this->inner->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'GroupedAnnotation',
      'inner' => $this->inner,
    ];
  }
}
