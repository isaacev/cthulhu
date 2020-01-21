<?php

namespace Cthulhu\ir\types\hm;

class LamExpr extends Expr {
  public Param $param;
  public Expr $body;
  public Type $note;

  public function __construct(Param $param, Expr $body, Type $note) {
    $this->param = $param;
    $this->body  = $body;
    $this->note  = $note;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->lambda()
      ->space()
      ->then($this->param)
      ->space()
      ->type($this->note)
      ->newline()
      ->increase_indentation()
      ->indent()
      ->then($this->body)
      ->paren_right()
      ->decrease_indentation();
  }
}
