<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class ExprStmt extends Stmt {
  public $expr;

  function __construct(Expr $expr) {
    parent::__construct($expr->span);
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
