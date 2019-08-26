<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class AssignStmt extends Stmt {
  public $identifier;
  public $expr;

  function __construct(IdentifierNode $ident, Expr $expr) {
    $this->identifier = $ident;
    $this->expr = $expr;
  }

  public function type(): Types\Type {
    return new Types\VoidType();
  }

  public function jsonSerialize() {
    return [
      'type' => 'AssignStmt',
      'identifier' => $this->identifier->jsonSerialize(),
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
