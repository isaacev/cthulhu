<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NativeFuncItem extends Item {
  public $name;
  public $polys;
  public $note;

  function __construct(Source\Span $span, IdentNode $name, array $polys, FunctionAnnotation $note, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name  = $name;
    $this->polys = $polys;
    $this->note  = $note;
  }
}
