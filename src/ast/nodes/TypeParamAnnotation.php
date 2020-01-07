<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class TypeParamAnnotation extends Annotation {
  public string $name;

  public function __construct(Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }
}
