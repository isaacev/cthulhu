<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class FuncExpr extends Expr {
  public $params;
  public $return_type;
  public $block;

  function __construct(array $params, Types\Type $return_type, BlockNode $block) {
    $this->params = $params;
    $this->return_type = $return_type;
    $this->block = $block;

    $param_types = array_map(function ($p) { return $p->type(); }, $params);
    $this->type = new Types\FuncType($param_types, $return_type);
  }

  public function type(): Types\Type {
    return $this->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncExpr',
      'params' => array_map(function ($p) { return $p->jsonSerialize(); }, $this->params),
      'return_type' => $this->return_type->jsonSerialize(),
      'block' => $this->block->jsonSerialize()
    ];
  }
}
