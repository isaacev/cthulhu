<?php

namespace Cthulhu\ast\nodes;

class MatchArm extends Node {
  public Pattern $pattern;
  public Expr $handler;

  public function __construct(Pattern $pattern, Expr $handler) {
    parent::__construct();
    $this->pattern = $pattern;
    $this->handler = $handler;
  }

  public function children(): array {
    return [ $this->pattern, $this->handler ];
  }
}
