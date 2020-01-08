<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;
use Cthulhu\val\StringValue;

class FieldNode extends Node {
  public Name $name;
  public Expr $expr;

  public function __construct(Name $name, Expr $expr) {
    parent::__construct();
    $this->name = $name;
    $this->expr = $expr;
  }

  public function to_children(): array {
    return [
      $this->name,
      $this->expr,
    ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1]);
  }

  public function build(): Builder {
    return (new Builder)
      ->value(StringValue::from_safe_scalar($this->name->value))
      ->space()
      ->fat_arrow()
      ->space()
      ->then($this->expr);
  }
}
