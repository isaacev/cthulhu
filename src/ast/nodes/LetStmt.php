<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class LetStmt extends Stmt {
  public LowerNameNode $name;
  public ?Annotation $note;
  public Expr $expr;

  /**
   * @param Span            $span
   * @param LowerNameNode   $name
   * @param Annotation|null $note
   * @param Expr            $expr
   * @param Attribute[]     $attrs
   */
  public function __construct(Span $span, LowerNameNode $name, ?Annotation $note, Expr $expr, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->note = $note;
    $this->expr = $expr;
  }
}
