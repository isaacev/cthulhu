<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class CallExpr extends Expr {
  public $callee;
  public $args;

  function __construct(Expr $callee, array $args) {
    $this->callee = $callee;
    $this->args = $args;
  }

  public function to_children(): array {
    return array_merge([ $this->callee ], $this->args);
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], array_slice($nodes, 1));
  }

  public function precedence(): int {
    return 40;
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->callee, $this->precedence())
      ->paren_left()
      ->each($this->args, (new Builder)
        ->comma()
        ->space())
      ->paren_right();
  }
}
