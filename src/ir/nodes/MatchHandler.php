<?php

namespace Cthulhu\ir\nodes;

class MatchHandler extends Node {
  public ReturnStmt $stmt;

  public function __construct(ReturnStmt $stmt) {
    parent::__construct();
    $this->stmt = $stmt;
  }

  public function children(): array {
    return [ $this->stmt ];
  }
}
