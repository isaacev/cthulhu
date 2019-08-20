<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class IfExpr extends Expr {
  public $condition;
  public $if_clause;
  public $else_clause;

  function __construct(Span $span, Expr $condition, BlockNode $if_clause, ?BlockNode $else_clause) {
    parent::__construct($span);
    $this->condition = $condition;
    $this->if_clause = $if_clause;
    $this->else_clause = $else_clause;
  }

  public function jsonSerialize() {
    return [
      'type' => 'IfExpr',
      'condition' => $this->condition->jsonSerialize(),
      'if_clause' => $this->if_clause->jsonSerialize(),
      'else_clause' => $this->else_clause ? $this->else_clause->jsonSerialize() : null
    ];
  }
}
