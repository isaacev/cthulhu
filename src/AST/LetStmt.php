<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class LetStmt extends Stmt {
  public $name;
  public $expr;

  function __construct(Span $span, string $name, Expr $expr) {
    parent::__construct($span);
    $this->name = $name;
    $this->expr = $expr;
  }

  public function jsonSerialize() {
    return [
      'type' => 'LetStmt',
      'name' => $this->name,
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
