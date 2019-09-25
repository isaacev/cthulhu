<?php

namespace Cthulhu\AST;

class ExprStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr, array $attrs) {
    parent::__construct($expr->span, $attrs);
    $this->expr = $expr;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('ExprStmt', $visitor_table)) {
      $visitor_table['ExprStmt']($this);
    }

    $this->expr->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'ExprStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
