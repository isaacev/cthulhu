<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class TupleAnnotation extends Annotation {
  public array $members;

  /**
   * @param Source\Span $span
   * @param Annotation[] $members
   */
  function __construct(Source\Span $span, array $members) {
    parent::__construct($span);
    assert(count($members) > 1);
    $this->members = $members;
  }
}
