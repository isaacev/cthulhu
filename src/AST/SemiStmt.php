<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;
use Cthulhu\Parser\Lexer\Token;

class SemiStmt extends Stmt {
  public $expr;
  public $semi;

  function __construct(Expr $expr, Token $semi) {
    parent::__construct($expr->span->extended_to($semi->span));
    $this->expr = $expr;
    $this->semi = $semi;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('SemiStmt', $visitor_table)) {
      $visitor_table['SemiStmt']($this);
    }

    $this->expr->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'SemiStmt',
      'expr' => $this->expr
    ];
  }
}
