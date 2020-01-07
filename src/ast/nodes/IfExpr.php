<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class IfExpr extends Expr {
  public Expr $condition;
  public BlockNode $if_clause;
  public ?BlockNode $else_clause;

  public function __construct(Span $span, Expr $condition, BlockNode $if_clause, ?BlockNode $else_clause) {
    parent::__construct($span);
    $this->condition   = $condition;
    $this->if_clause   = $if_clause;
    $this->else_clause = $else_clause;
  }
}
