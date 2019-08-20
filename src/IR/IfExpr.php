<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class IfExpr extends Expr {
  public $type;
  public $condition;
  public $if_block;
  public $else_block;

  function __construct(Type $type, Expr $condition, BlockNode $if_block, ?BlockNode $else_block) {
    $this->type = $type;
    $this->condition = $condition;
    $this->if_block = $if_block;
    $this->else_block = $else_block;
  }

  public function type(): Type {
    return $this->type;
  }

  public function jsonSerialize() {
    return [
      'kind' => 'IfExpr',
      'type' => $this->type->jsonSerialize(),
      'condition' => $this->condition->jsonSerialize(),
      'if_block' => $this->if_block->jsonSerialize(),
      'else_block' => $this->else_block ? $this->else_block->jsonSerialize() : null
    ];
  }
}
