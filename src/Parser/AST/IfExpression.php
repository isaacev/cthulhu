<?php

namespace Cthulhu\Parser\AST;

class IfExpression extends Expression {
  public $condition;
  public $if_clause;
  public $else_clause;

  function __construct(Expression $condition, Block $if_clause, ?Block $else_clause) {
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
