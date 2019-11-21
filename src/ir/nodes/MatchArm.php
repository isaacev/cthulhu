<?php

namespace Cthulhu\ir\nodes;

class MatchArm extends Node {
  public $pattern;
  public $handler;

  function __construct(Pattern $pattern, MatchHandler $handler) {
    parent::__construct();
    $this->pattern = $pattern;
    $this->handler = $handler;
  }

  function children(): array {
    return [
      $this->pattern,
      $this->handler,
    ];
  }
}
