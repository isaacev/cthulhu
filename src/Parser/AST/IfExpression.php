<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class IfExpression extends Expression {
  public $from;
  public $condition;
  public $if_clause;
  public $else_clause;

  function __construct(Point $from, Expression $condition, Block $if_clause, ?Block $else_clause) {
    $this->from = $from;
    $this->condition = $condition;
    $this->if_clause = $if_clause;
    $this->else_clause = $else_clause;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->from;
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
