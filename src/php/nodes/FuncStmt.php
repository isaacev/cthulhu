<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\Builder;

class FuncStmt extends Stmt {
  public FuncHead $head;
  public BlockNode $body;
  public array $attrs;

  public function __construct(FuncHead $head, BlockNode $body, array $attrs, ?Stmt $next) {
    parent::__construct($next);
    $this->head  = $head;
    $this->body  = $body;
    $this->attrs = $attrs;
  }

  public function children(): array {
    return [ $this->head, $this->body ];
  }

  public function from_children(array $nodes): FuncStmt {
    return (new FuncStmt($nodes[0], $nodes[1], $this->attrs, $this->next))
      ->copy($this);
  }

  public function from_successor(?EditableSuccessor $successor): FuncStmt {
    assert($successor === null || $successor instanceof Stmt);
    return (new FuncStmt($this->head, $this->body, $this->attrs, $successor))
      ->copy($this);
  }

  public function build(): Builder {
    $commented_attrs = [];
    foreach ($this->attrs as $name => $value) {
      $commented_attrs[] = (new Builder)
        ->comment('#[' . $name . ']')
        ->newline_then_indent();
    }

    return (new Builder)
      ->newline_then_indent()
      ->each($commented_attrs)
      ->then($this->head)
      ->space()
      ->then($this->body)
      ->maybe($this->next instanceof self, (new Builder)->newline())
      ->then($this->next);
  }
}
