<?php

namespace Cthulhu\ir\nodes;

class MatchHandler extends Node {
  public $stmt;

  function __construct(ReturnStmt $stmt) {
    parent::__construct();
    $this->stmt = $stmt;
  }

  function children(): array {
    return [ $this->stmt ];
  }
}
