<?php

namespace Cthulhu\IR;

class IfExpr extends Expr {
  public $type;
  public $condition;
  public $if_block;
  public $else_block;

  function __construct(Types\Type $type, Expr $condition, BlockNode $if_block, ?BlockNode $else_block) {
    $this->type       = $type;
    $this->condition  = $condition;
    $this->if_block   = $if_block;
    $this->else_block = $else_block;
  }

  public function return_type(): Types\Type {
    return $this->type;
  }
}
