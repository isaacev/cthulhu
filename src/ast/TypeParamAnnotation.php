<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class TypeParamAnnotation extends Annotation {
  public string $name;

  function __construct(Source\Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }
}
