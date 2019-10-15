<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class TupleAnnotation extends Annotation {
  public $members;

  function __construct(Source\Span $span, array $members) {
    parent::__construct($span);
    assert(!empty($members));
    $this->members = $members;
  }
}
