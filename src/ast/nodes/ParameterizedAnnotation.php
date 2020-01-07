<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class ParameterizedAnnotation extends Annotation {
  public Annotation $inner;
  public array $params;

  /**
   * @param Span         $span
   * @param Annotation   $inner
   * @param Annotation[] $params
   */
  public function __construct(Span $span, Annotation $inner, array $params) {
    parent::__construct($span);
    assert(!empty($params));
    $this->inner  = $inner;
    $this->params = $params;
  }
}
