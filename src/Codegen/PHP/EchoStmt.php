<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class EchoStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    $this->expr = $expr;
  }

  public function to_children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0]);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('echo')
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }
}
