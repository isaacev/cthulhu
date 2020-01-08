<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class CastExpr extends Expr {
  public string $to_type;
  public Expr $expr;

  public function __construct(string $to_type, Expr $expr) {
    parent::__construct();
    $this->to_type = $to_type;
    $this->expr    = $expr;
  }

  public function to_children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->to_type, $nodes[0]);
  }

  public function precedence(): int {
    return Precedence::CAST;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword($this->to_type)
      ->paren_right()
      ->then($this->expr);
  }
}
