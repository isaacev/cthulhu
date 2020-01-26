<?php

namespace Cthulhu\php;

class StatementAccumulator {
  private ExpressionStack $exprs;
  private array $pending_stmts = [];
  private array $return_vars = [];

  public function __construct(ExpressionStack $exprs) {
    $this->exprs = $exprs;
  }

  public function push_block(?nodes\Variable $ret_var): void {
    array_push($this->pending_stmts, []);
    array_push($this->return_vars, $ret_var);
  }

  public function peek_return_var(): nodes\Variable {
    assert(!empty($this->return_vars));
    return end($this->return_vars);
  }

  public function pop_block(): nodes\BlockNode {
    array_pop($this->return_vars);
    $stmts = array_pop($this->pending_stmts);
    return new nodes\BlockNode($stmts);
  }

  public function push_stmt(nodes\Stmt $stmt): void {
    assert(!empty($this->pending_stmts));
    array_push($this->pending_stmts[count($this->pending_stmts) - 1], $stmt);
  }
}
