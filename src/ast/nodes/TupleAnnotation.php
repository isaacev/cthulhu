<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class TupleAnnotation extends Annotation {
  public array $members;

  /**
   * @param Span         $span
   * @param Annotation[] $members
   */
  public function __construct(Span $span, array $members) {
    parent::__construct($span);
    assert(count($members) > 1);
    $this->members = $members;
  }
}
