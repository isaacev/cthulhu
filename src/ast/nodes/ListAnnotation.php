<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class ListAnnotation extends Annotation {
  public Annotation $elements;

  public function __construct(Span $span, Annotation $elements) {
    parent::__construct($span);
    $this->elements = $elements;
  }
}
