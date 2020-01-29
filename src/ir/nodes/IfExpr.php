<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;
use Cthulhu\lib\trees\EditableNodelike;

class IfExpr extends Expr {
  public Expr $condition;
  public Stmts $consequent;
  public Stmts $alternate;

  public function __construct(Type $type, Expr $condition, Stmts $consequent, Stmts $alternate) {
    parent::__construct($type);
    $this->condition  = $condition;
    $this->consequent = $consequent;
    $this->alternate  = $alternate;
  }

  public function children(): array {
    return [ $this->condition, $this->consequent, $this->alternate ];
  }

  public function from_children(array $children): EditableNodelike {
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
