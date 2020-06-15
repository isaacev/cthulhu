<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\Builder;
use Cthulhu\val\IntegerValue;

class ExitStmt extends Stmt {
  public IntegerValue $exit_code;

  public function __construct(IntegerValue $exit_code, ?Stmt $next) {
    parent::__construct($next);
    $this->exit_code = $exit_code;
  }

  use traits\Atomic;

  public function from_successor(?EditableSuccessor $successor): self {
    assert($successor === null || $successor instanceof Stmt);
    return new self($this->exit_code, $successor);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline_then_indent()
      ->keyword('exit')
      ->paren_left()
      ->value($this->exit_code)
      ->paren_right()
      ->semicolon()
      ->then($this->next);
  }
}
