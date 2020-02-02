<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\val\StringValue;

class DieStmt extends Stmt {
  public StringValue $message;

  public function __construct(StringValue $message) {
    parent::__construct();
    $this->message = $message;
  }

  public function children(): array {
    return [ $this->message ];
  }

  public function from_children(array $children): DieStmt {
    return new DieStmt(...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('die')
      ->paren_left()
      ->value($this->message)
      ->paren_right()
      ->semicolon();
  }
}
