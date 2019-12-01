<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class LetStmt extends Stmt {
  public LowerNameNode $name;
  public ?Annotation $note;
  public Expr $expr;

  /**
   * @param Source\Span $span
   * @param LowerNameNode $name
   * @param Annotation|null $note
   * @param Expr $expr
   * @param Attribute[] $attrs
   */
  function __construct(Source\Span $span, LowerNameNode $name, ?Annotation $note, Expr $expr, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->note = $note;
    $this->expr = $expr;
  }
}
