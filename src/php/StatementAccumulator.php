<?php

namespace Cthulhu\php;

class StatementAccumulator {
  private ExpressionStack $exprs;

  /* @var nodes\Stmt[]|null[] $pending_stmts */
  private array $pending_stmts = [];
  private array $return_vars = [];
  private array $block_stash = [];
  private array $yield_strategy = [];

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
    array_push($this->pending_stmts, null);
  }

  public function peek_return_var(): nodes\Variable {
    assert(!empty($this->return_vars));
    return end($this->return_vars);
  }

  public function pop_block(): nodes\BlockNode {
    $stmt = array_pop($this->pending_stmts);
    return new nodes\BlockNode($stmt);
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
    $current_stmt = end($this->pending_stmts);
    if ($current_stmt === null) {
      array_pop($this->pending_stmts);
      array_push($this->pending_stmts, $stmt);
      assert(end($this->pending_stmts) === $stmt);
    } else {
      $current_stmt->mutable_append($stmt);
      assert(end($this->pending_stmts)->last_stmt() === $stmt);
    }
  }

  public function peek_yield_strategy(): string {
    assert(!empty($this->yield_strategy));
    return end($this->yield_strategy);
  }

  public function push_yield_strategy(string $strategy): void {
    array_push($this->yield_strategy, $strategy);
  }

  public function copy_yield_strategy(): void {
    $current = $this->peek_yield_strategy();
    $this->push_yield_strategy($current);
  }

  public function pop_yield_strategy(): string {
    assert(!empty($this->yield_strategy));
    return array_pop($this->yield_strategy);
  }
}
