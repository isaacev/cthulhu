<?php

namespace Cthulhu\ast\nodes;

class OperatorRef extends Expr implements FnName {
  public Operator $oper;

  public function __construct(Operator $oper) {
    parent::__construct();
    $this->oper = $oper;
  }

  public function children(): array {
    return [ $this->oper ];
  }
}
