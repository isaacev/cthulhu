<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class VarDumpStmt extends Stmt {
  public Expr $expr;

  public function __construct(Expr $expr) {
    parent::__construct();
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0]);
  }

  public function build(): Builder {
    return (new Builder)
      ->identifier('var_dump')
      ->paren_left()
      ->then($this->expr)
      ->paren_right()
      ->semicolon();
  }
}
