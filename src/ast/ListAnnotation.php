<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class ListAnnotation extends Annotation {
  public $elements;

  function __construct(Source\Span $span, Annotation $elements) {
    parent::__construct($span);
    $this->elements = $elements;
  }
}
