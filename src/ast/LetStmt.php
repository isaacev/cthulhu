<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class LetStmt extends Stmt {
  public $name;
  public $note;
  public $expr;

  function __construct(Source\Span $span, LowerNameNode $name, ?Annotation $note, Expr $expr, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->note = $note;
    $this->expr = $expr;
  }
}
