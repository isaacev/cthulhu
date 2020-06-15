<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\val\StringValue;

class FieldNode extends Expr {
  public StringValue $key;
  public Expr $expr;

  public function __construct(StringValue $key, Expr $expr) {
    parent::__construct();
    $this->key  = $key;
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->key, $nodes[1]);
  }

  public function build(): Builder {
    return (new Builder)
      ->value($this->key)
      ->space()
      ->fat_arrow()
      ->space()
      ->then($this->expr);
  }
}
