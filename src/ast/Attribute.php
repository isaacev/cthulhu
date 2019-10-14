<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class Attribute extends Node {
  public $name;

  function __construct(Source\Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('Attribute', $visitor_table)) {
      $visitor_table['Attribute']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'Attribute',
      'name' => $this->name
    ];
  }
}
