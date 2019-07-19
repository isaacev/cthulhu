<?php

namespace Cthulhu\Parser\AST;

class NamedAnnotation extends Annotation {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }

  public function jsonSerialize() {
    return [
      'type' => 'NamedAnnotation',
      'name' => $this->name
    ];
  }
}
