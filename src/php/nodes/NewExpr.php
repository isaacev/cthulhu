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
  public function __construct(ReferenceExpr $ref, array $args) {
    parent::__construct();
    $this->ref  = $ref;
    $this->args = $args;
  }

  public function to_children(): array {
    return array_merge(
      [ $this->ref ],
      $this->args
    );
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], array_slice($nodes, 1));
  }

  public function precedence(): int {
    return Precedence::CLONE_AND_NEW;
  }

  public function build(): Builder {
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
