<?php

namespace Cthulhu\ir\nodes;

class MatchArm extends Node {
  public Pattern $pattern;
  public MatchHandler $handler;

  public function __construct(Pattern $pattern, MatchHandler $handler) {
    parent::__construct();
    $this->pattern = $pattern;
    $this->handler = $handler;
  }

  public function children(): array {
    return [
      $this->pattern,
      $this->handler,
    ];
  }
}
