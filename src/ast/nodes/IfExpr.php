<?php

namespace Cthulhu\ast\nodes;

class IfExpr extends Expr {
  public Expr $condition;
  public BlockNode $consequent;
  public ?BlockNode $alternate;

  public function __construct(Expr $condition, BlockNode $consequent, ?BlockNode $alternate) {
    parent::__construct();
    $this->condition  = $condition;
    $this->consequent = $consequent;
    $this->alternate  = $alternate;
  }

  public function children(): array {
    return [ $this->condition, $this->consequent, $this->alternate ];
  }
}
