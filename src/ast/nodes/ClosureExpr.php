<?php

namespace Cthulhu\ast\nodes;

class ClosureExpr extends Expr {
  public ClosureParams $params;
  public BlockNode $body;

  /**
   * @param ClosureParams $params
   * @param BlockNode     $body
   */
  public function __construct(ClosureParams $params, BlockNode $body) {
    parent::__construct();
    $this->params = $params;
    $this->body   = $body;
  }

  public function children(): array {
    return [ $this->params, $this->body ];
  }
}
