<?php

namespace Cthulhu\ast;

use Cthulhu\Parser\Lexer\Token;

class SemiStmt extends Stmt {
  public Expr $expr;
  public Token $semi;

  /**
   * @param Expr $expr
   * @param Token $semi
   * @param Attribute[] $attrs
   */
  function __construct(Expr $expr, Token $semi, array $attrs) {
    parent::__construct($expr->span->extended_to($semi->span), $attrs);
    $this->expr = $expr;
    $this->semi = $semi;
  }
}
