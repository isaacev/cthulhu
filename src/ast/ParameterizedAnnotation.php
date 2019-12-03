<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class ParameterizedAnnotation extends Annotation {
  public Annotation $inner;
  public array $params;

  /**
   * @param Source\Span  $span
   * @param Annotation   $inner
   * @param Annotation[] $params
   */
  function __construct(Source\Span $span, Annotation $inner, array $params) {
    parent::__construct($span);
    assert(!empty($params));
    $this->inner  = $inner;
    $this->params = $params;
  }
}
