<?php

namespace Cthulhu\php;

class StatementAccumulator {
  private ExpressionStack $exprs;
  private array $pending_stmts = [];
  private array $return_vars = [];
  private array $block_stash = [];

  public function __construct(ExpressionStack $exprs) {
    $this->exprs = $exprs;
  }

  public function push_return_var(?nodes\Variable $ret_var): void {
    array_push($this->return_vars, $ret_var);
  }

  public function pop_return_var(): ?nodes\Variable {
    return array_pop($this->return_vars);
  }

  public function push_block(): void {
    array_push($this->pending_stmts, []);
  }

  public function peek_return_var(): nodes\Variable {
    assert(!empty($this->return_vars));
    return end($this->return_vars);
  }

  public function pop_block(): nodes\BlockNode {
    $stmts = array_pop($this->pending_stmts);
    return new nodes\BlockNode($stmts);
  }

  public function stash_block(nodes\BlockNode $block): void {
    array_push($this->block_stash, $block);
  }

  public function unstash_block(): nodes\BlockNode {
    assert(!empty($this->block_stash));
    return array_pop($this->block_stash);
  }

  public function push_stmt(nodes\Stmt $stmt): void {
    assert(!empty($this->pending_stmts));
    array_push($this->pending_stmts[count($this->pending_stmts) - 1], $stmt);
  }
}
