<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class GroupedAnnotation extends Annotation {
  public Annotation $inner;

  public function __construct(Span $span, Annotation $inner) {
    parent::__construct($span);
    $this->inner = $inner;
  }
}
