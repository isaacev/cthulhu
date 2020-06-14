<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\Builder;
use Cthulhu\val\StringValue;

class DieStmt extends Stmt {
  public StringValue $message;

  public function __construct(StringValue $message, ?Stmt $next) {
    parent::__construct($next);
    $this->message = $message;
  }

  use traits\Atomic;

  public function from_successor(?EditableSuccessor $successor): DieStmt {
    assert($successor === null || $successor instanceof Stmt);
    return new DieStmt($this->message, $successor);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline_then_indent()
      ->keyword('die')
      ->paren_left()
      ->value($this->message)
      ->paren_right()
      ->semicolon()
      ->then($this->next);
  }
}
