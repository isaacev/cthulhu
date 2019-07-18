<?php

namespace Cthulhu\Parser\AST;

class NameAnnotation extends Annotation {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }

  public function jsonSerialize() {
    return [
      'type' => 'NameAnnotation',
      'name' => $this->name
    ];
  }
}
