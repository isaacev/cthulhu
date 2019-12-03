<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class NewExpr extends Expr {
  public ReferenceExpr $ref;
  public array $args;

  /**
   * @param ReferenceExpr $ref
   * @param Expr[]        $args
   */
  function __construct(ReferenceExpr $ref, array $args) {
    parent::__construct();
    $this->ref  = $ref;
    $this->args = $args;
  }

  function to_children(): array {
    return array_merge(
      [ $this->ref ],
      $this->args
    );
  }

  function from_children(array $nodes): Node {
    return new self($nodes[0], array_slice($nodes, 1));
  }

  function precedence(): int {
    return Precedence::CLONE_AND_NEW;
  }

  function build(): Builder {
    return (new Builder)
      ->keyword('new')
      ->space()
      ->then($this->ref)
      ->paren_left()
      ->each($this->args, (new Builder)
        ->comma()
        ->space())
      ->paren_right();
  }
}
