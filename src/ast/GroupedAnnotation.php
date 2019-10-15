<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class GroupedAnnotation extends Annotation {
  public $inner;

  function __construct(Source\Span $span, Annotation $inner) {
    parent::__construct($span);
    $this->inner = $inner;
  }
}
