<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class FuncStmt extends Stmt {
  public $name;
  public $params;
  public $body;
  public $attrs;

  function __construct(Name $name, array $params, BlockNode $body, array $attrs) {
    $this->name = $name;
    $this->params = $params;
    $this->body = $body;
    $this->attrs = $attrs;
  }

  public function to_children(): array {
    return [ $this->body ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->name, $this->params, $nodes[0], $this->attrs);
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
      ->keyword('function')
      ->space()
      ->then($this->name)
      ->paren_left()
      ->each($this->params, (new Builder)->comma()->space())
      ->paren_right()
      ->space()
      ->then($this->body);
  }
}
