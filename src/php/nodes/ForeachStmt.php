<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\Builder;

class ForEachStmt extends Stmt {
  public Expr $source;
  public ?Variable $index;
  public Variable $pointer;
  public BlockNode $body;

  public function __construct(Expr $source, ?Variable $index, Variable $pointer, BlockNode $body, ?Stmt $next) {
    parent::__construct($next);
    $this->source  = $source;
    $this->index   = $index;
    $this->pointer = $pointer;
    $this->body    = $body;
  }

  public function children(): array {
    return [ $this->source, $this->index, $this->pointer, $this->body ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1], $nodes[2], $nodes[3], $this->next);
  }

  public function from_successor(?EditableSuccessor $successor): ForEachStmt {
    assert($successor === null || $successor instanceof Stmt);
    return new ForEachStmt($this->source, $this->index, $this->pointer, $this->body, $successor);
  }

  public function build(): Builder {
    if ($this->index) {
      $index_binding = (new Builder)
        ->then($this->index)
        ->space()
        ->operator('=>')
        ->space();
    } else {
      $index_binding = (new Builder);
    }

    return (new Builder)
      ->newline_then_indent()
      ->keyword('foreach')
      ->space()
      ->paren_left()
      ->then($this->source)
      ->space()
      ->keyword('as')
      ->space()
      ->then($index_binding)
      ->then($this->pointer)
      ->paren_right()
      ->space()
      ->then($this->body)
      ->then($this->next ?? (new Builder));
  }
}
