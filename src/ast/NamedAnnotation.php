<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NamedAnnotation extends Annotation {
  public $path;

  function __construct(PathNode $path) {
    parent::__construct($path->span);
    $this->path = $path;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('NamedAnnotation', $visitor_table)) {
      $visitor_table['NamedAnnotation']($this);
    }

    $this->path->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'NamedAnnotation',
      'path' => $this->path
    ];
  }
}
