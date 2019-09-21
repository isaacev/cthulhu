<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class IfExpr extends Expr {
  public $condition;
  public $if_clause;
  public $else_clause;

  function __construct(Source\Span $span, Expr $condition, BlockNode $if_clause, ?BlockNode $else_clause) {
    parent::__construct($span);
    $this->condition = $condition;
    $this->if_clause = $if_clause;
    $this->else_clause = $else_clause;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('IfExpr', $visitor_table)) {
      $visitor_table['IfExpr']($this);
    }

    $this->condition->visit($visitor_table);
    $this->if_clause->visit($visitor_table);
    if ($this->else_clause) {
      $this->else_clause->visit($visitor_table);
    }
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
