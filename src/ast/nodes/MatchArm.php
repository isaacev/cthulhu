<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class MatchArm extends Node {
  public Pattern $pattern;
  public Expr $handler;

  public function __construct(Span $span, Pattern $pattern, Expr $handler) {
    parent::__construct($span);
    $this->pattern = $pattern;
    $this->handler = $handler;
  }
}
