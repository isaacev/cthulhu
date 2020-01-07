<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class WildcardPattern extends Pattern {
  public function __construct(Span $span) {
    parent::__construct($span);
  }
}
