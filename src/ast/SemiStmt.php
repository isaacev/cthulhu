<?php

namespace Cthulhu\ast;

use Cthulhu\Source;
use Cthulhu\Parser\Lexer\Token;

class SemiStmt extends Stmt {
  public $expr;
  public $semi;

  function __construct(Expr $expr, Token $semi, array $attrs) {
    parent::__construct($expr->span->extended_to($semi->span), $attrs);
    $this->expr = $expr;
    $this->semi = $semi;
  }
}
