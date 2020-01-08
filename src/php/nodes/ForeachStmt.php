<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ForEachStmt extends Stmt {
  public Expr $source;
  public ?Variable $index;
  public Variable $pointer;
  public BlockNode $body;

  public function __construct(Expr $source, ?Variable $index, Variable $pointer, BlockNode $body) {
    parent::__construct();
    $this->source  = $source;
    $this->index   = $index;
    $this->pointer = $pointer;
    $this->body    = $body;
  }

  public function to_children(): array {
    return [ $this->source, $this->index, $this->pointer, $this->body ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1], $nodes[2], $nodes[3]);
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
      ->then($this->body);
  }
}
