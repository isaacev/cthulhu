<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class Block extends Expr {
  public ?Stmt $stmt;

  public function __construct(Type $type, ?Stmt $stmt) {
    parent::__construct($type);
    $this->stmt = $stmt;
  }

  public function children(): array {
    return [ $this->stmt ];
  }

  public function from_children(array $children): Block {
    return new Block($this->type, ...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->stmts($this->stmt);
  }
}
