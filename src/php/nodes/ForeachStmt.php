<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ForEachStmt extends Stmt {
  public Expr $source;
  public Variable $pointer;
  public BlockNode $body;

  public function __construct(Expr $source, Variable $pointer, BlockNode $body) {
    parent::__construct();
    $this->source  = $source;
    $this->pointer = $pointer;
    $this->body    = $body;
  }

  public function to_children(): array {
    return [ $this->source, $this->pointer, $this->body ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1], $nodes[2]);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('foreach')
      ->space()
      ->paren_left()
      ->then($this->source)
      ->space()
      ->keyword('as')
      ->space()
      ->then($this->pointer)
      ->paren_right()
      ->space()
      ->then($this->body);
  }
}
