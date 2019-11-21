<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class WildcardPattern extends Pattern {
  function __construct(Source\Span $span) {
    parent::__construct($span);
  }
}
