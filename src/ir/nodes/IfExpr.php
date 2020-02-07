<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class IfExpr extends BranchExpr {
  public Expr $condition;
  public Consequent $consequent;
  public Alternate $alternate;

  public function __construct(Type $type, Expr $condition, Consequent $consequent, Alternate $alternate) {
    parent::__construct($type);
    $this->condition  = $condition;
    $this->consequent = $consequent;
    $this->alternate  = $alternate;
  }

  public function children(): array {
    return [ $this->condition, $this->consequent, $this->alternate ];
  }

  public function from_children(array $children): IfExpr {
    return new IfExpr($this->type, ...$children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('if')
      ->space()
      ->then($this->condition)
      ->increase_indentation()
      ->newline()
      ->indent()
      ->then($this->consequent)
      ->newline()
      ->indent()
      ->then($this->alternate)
      ->decrease_indentation()
      ->paren_right();
  }
}
