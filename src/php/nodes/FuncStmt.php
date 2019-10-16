<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class FuncStmt extends Stmt {
  public $head;
  public $body;
  public $attrs;

  function __construct(FuncHead $head, BlockNode $body, array $attrs) {
    parent::__construct();
    $this->head = $head;
    $this->body = $body;
    $this->attrs = $attrs;
  }

  public function to_children(): array {
    return [ $this->head, $this->body ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1], $this->attrs);
  }

  public function build(): Builder {
    $commented_attrs = [];
    foreach ($this->attrs as $name => $value) {
      $commented_attrs[] = (new Builder)
        ->comment('#[' . $name . ']')
        ->newline_then_indent();
    }

    return (new Builder)
      ->each($commented_attrs)
      ->then($this->head)
      ->space()
      ->then($this->body);
  }
}
