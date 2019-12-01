<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class CallExpr extends Expr {
  public Expr $callee;
  public array $args;

  /**
   * @param Expr $callee
   * @param Expr[] $args
   */
  function __construct(Expr $callee, array $args) {
    parent::__construct();
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
    return Precedence::HIGHEST;
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
