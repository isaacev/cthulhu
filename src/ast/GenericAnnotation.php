<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class GenericAnnotation extends Annotation {
  public $name;

  function __construct(Source\Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('GenericAnnotation', $visitor_table)) {
      $visitor_table['GenericAnnotation']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'GenericAnnotation',
      'name' => $this->name
    ];
  }
}
