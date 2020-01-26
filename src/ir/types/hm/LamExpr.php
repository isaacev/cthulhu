<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

class LamExpr extends Expr {
  public Param $param;
  public Expr $body;
  public Type $note;
  public ?Span $note_span;

  public function __construct(Spanlike $spanlike, Param $param, Expr $body, Type $note, ?Span $note_span) {
    parent::__construct($spanlike);
    $this->param     = $param;
    $this->body      = $body;
    $this->note      = $note;
    $this->note_span = $note_span;
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
