<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\Builder;

class ContinueStmt extends Stmt {
  use traits\Atomic;

  public function from_successor(?EditableSuccessor $successor): ContinueStmt {
    assert($successor === null || $successor instanceof Stmt);
    return new ContinueStmt($successor);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline_then_indent()
      ->keyword('continue')
      ->semicolon()
      ->then($this->next);
  }
}
