<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class NamedAnnotation extends Annotation {
  public $name;

  function __construct(Source\Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('NamedAnnotation', $visitor_table)) {
      $visitor_table['NamedAnnotation']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'NamedAnnotation',
      'name' => $this->name
    ];
  }
}
