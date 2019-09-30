<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class ReturnStmt extends Stmt {
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
      ->keyword('return')
      ->space()
      ->expr($this->expr)
      ->semicolon();
  }

  public function jsonSerialize() {
    return [
      'type' => 'ReturnStmt'
    ];
  }
}
