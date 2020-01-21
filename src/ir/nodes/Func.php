<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\hm;
use Cthulhu\lib\trees\EditableNodelike;

class Func extends Expr {
  public Names $names;
  public ?Stmt $stmt;

  public function __construct(hm\Func $type, Names $names, ?Stmt $stmt) {
    parent::__construct($type);
    $this->names = $names;
    $this->stmt  = $stmt;
  }

  public function children(): array {
    return [ $this->names, $this->stmt ];
  }

  public function from_children(array $children): EditableNodelike {
    assert($this->type instanceof hm\Func);
    return (new self($this->type, $children[0], $children[1]))
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
      ->then($stmt)
      ->paren_right();
  }
}
