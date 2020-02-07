<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Func;

class Closure extends Expr {
  public Func $func_type;
  public Names $names;
  public Names $closed;
  public ?Stmt $stmt;

  public function __construct(Func $type, Names $names, Names $closed, ?Stmt $stmt) {
    parent::__construct($type);
    $this->func_type = $type;
    $this->names     = $names;
    $this->closed    = $closed;
    $this->stmt      = $stmt;
  }

  public function children(): array {
    return [ $this->names, $this->closed, $this->stmt ];
  }

  public function from_children(array $children): Closure {
    assert($this->type instanceof Func);
    return (new Closure($this->type, $children[0], $children[1], $children[2]))
      ->copy($this);
  }

  public function build(): Builder {
    if ($this->stmt) {
      $stmt = (new Builder)
        ->paren_left()
        ->increase_indentation()
        ->then($this->stmt)
        ->decrease_indentation()
        ->paren_right();
    } else {
      $stmt = (new Builder)
        ->paren_left()
        ->paren_right();
    }

    return (new Builder)
      ->paren_left()
      ->keyword('λ')
      ->space()
      ->then($this->names)
      ->space()
      ->then($this->closed)
      ->space()
      ->then($stmt)
      ->paren_right();
  }
}
