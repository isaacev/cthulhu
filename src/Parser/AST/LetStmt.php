<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class LetStmt extends Stmt {
  public $name;
  public $expr;

  function __construct(Span $span, string $name, expr $expr) {
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
