<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class IfExpr extends Expr {
  public $condition;
  public $if_clause;
  public $else_clause;

  function __construct(Span $span, Expr $condition, array $if_clause, ?array $else_clause) {
    parent::__construct($span);
    $this->condition = $condition;
    $this->if_clause = $if_clause;
    $this->else_clause = $else_clause;
  }

  public function jsonSerialize() {
    $if_clause_json = array_map(function ($stmt) {
      return $stmt->jsonSerialize();
    }, $this->if_clause);

    $else_clause_json = $this->else_clause ? array_map(function ($stmt) {
      return $stmt->jsonSerialize();
    }, $this->else_clause) : null;

    return [
      'type' => 'IfExpr',
      'condition' => $this->condition->jsonSerialize(),
      'if_clause' => $if_clause_json,
      'else_clause' => $else_clause_json
    ];
  }
}
