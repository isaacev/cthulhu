<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class PropertyAccessExpr extends Expr {
  public Expr $expr;
  public Variable $prop;

  public function __construct(Expr $expr, Variable $prop) {
    parent::__construct();
    $this->expr = $expr;
    $this->prop = $prop;
  }

  public function children(): array {
    return [
      $this->expr,
      $this->prop,
    ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1]);
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->expr, $this->precedence())
      ->thin_arrow()
      ->identifier($this->prop->value);
  }
}
