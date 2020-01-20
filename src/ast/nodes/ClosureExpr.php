<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class ClosureExpr extends Expr {
  public array $params;
  public array $body;

  /**
   * @param Span        $span
   * @param ParamNode[] $params
   * @param Stmt[]      $body
   */
  public function __construct(Span $span, array $params, array $body) {
    parent::__construct($span);
    $this->params = $params;
    $this->body   = $body;
  }
}
