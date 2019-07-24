<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class ExprStmt extends Stmt {
  public $expr;

  function __construct(Span $span, Expr $expr) {
    parent::__construct($span);
    $this->expr = $expr;
  }

  public function jsonSerialize() {
    return [
      'type' => 'ExprStmt',
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
