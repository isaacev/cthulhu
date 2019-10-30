<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NativeFuncItem extends Item {
  public $name;
  public $note;

  function __construct(Source\Span $span, IdentNode $name, FunctionAnnotation $note, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name  = $name;
    $this->note  = $note;
  }
}
