<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class TernaryExpr extends Expr {
  public Expr $cond;
  public Expr $if_true;
  public Expr $if_false;

  function __construct(Expr $cond, Expr $if_true, Expr $if_false) {
    parent::__construct();
    $this->cond = $cond;
    $this->if_true = $if_true;
    $this->if_false = $if_false;
  }

  public function to_children(): array {
    return [ $this->cond, $this->if_true, $this->if_false ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1], $nodes[2]);
  }

  public function precedence(): int {
    return Precedence::TERNARY;
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->cond, $this->precedence())
      ->space()
      ->operator('?')
      ->space()
      ->expr($this->if_true, $this->precedence())
      ->space()
      ->operator(':')
      ->space()
      ->expr($this->if_false, $this->precedence());
  }
}
