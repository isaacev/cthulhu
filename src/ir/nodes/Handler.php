<?php

namespace Cthulhu\ir\nodes;

class Handler extends Node {
  public Stmt $stmt;

  public function __construct(Stmt $stmt) {
    parent::__construct();
    $this->stmt = $stmt;
  }

  public function children(): array {
    return [ $this->stmt ];
  }

  public function from_children(array $children): Handler {
    return new Handler(...$children);
  }

  public function build(): Builder {
    return $this->stmt->build();
  }
}
