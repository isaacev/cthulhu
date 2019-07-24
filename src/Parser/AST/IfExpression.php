<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class IfExpression extends Expression {
  public $condition;
  public $if_clause;
  public $else_clause;

  function __construct(Span $span, Expression $condition, Block $if_clause, ?Block $else_clause) {
    parent::__construct($span);
    $this->condition = $condition;
    $this->if_clause = $if_clause;
    $this->else_clause = $else_clause;
  }

  public function jsonSerialize() {
    return [
      'type' => 'IfExpression',
      'condition' => $this->condition->jsonSerialize(),
      'if_clause' => $this->if_clause->jsonSerialize(),
      'else_clause' => $this->else_clause ? $this->else_clause->jsonSerialize() : null
    ];
  }
}
