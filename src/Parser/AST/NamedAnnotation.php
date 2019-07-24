<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class NamedAnnotation extends Annotation {
  public $name;

  function __construct(Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }

  public function jsonSerialize() {
    return [
      'type' => 'NamedAnnotation',
      'name' => $this->name
    ];
  }
}
