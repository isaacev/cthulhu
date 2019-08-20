<?php

namespace Cthulhu\Codegen;

use Cthulhu\IR;

class Codegen {
  public static function generate(IR\RootNode $root): PHP\RootNode {
    $cg = new Codegen();
    return new PHP\RootNode($cg->block($root->block));
  }

  private $pending_blocks;
  private $pending_scopes;

  private function __construct() {
    $this->pending_blocks = [];
    $this->pending_scopes = [new PHP\GlobalScope()];
  }

  private function current_scope(): PHP\Scope {
    return $this->pending_scopes[count($this->pending_scopes) - 1];
  }

  private function push_scope(PHP\Scope $scope): void {
    array_push($this->pending_scopes, $scope);
  }

  private function pop_scope(): PHP\Scope {
    return array_pop($this->pending_scopes);
  }

  private function block(IR\BlockNode $block): PHP\BlockNode {
    $pending_block = new PendingBlock($block->scope);
    array_push($this->pending_blocks, $pending_block);
    foreach ($block->stmts as $stmt) {
      $pending_block->push_stmt($this->stmt($stmt));
    }
    array_pop($this->pending_blocks);
    return new PHP\BlockNode($pending_block->stmts);
  }

  private function block_with_assignment(IR\BlockNode $block, string $var): PHP\BlockNode {
    if ($block->length() === 0) {
      throw new \Exception('cannot set assignment in empty block');
    }

    $pending_block = new PendingBlock($block->scope);
    array_push($this->pending_blocks, $pending_block);
    for ($i = 0, $len = $block->length(); $i < $len; $i++) {
      $stmt = $block->stmts[$i];
      if ($i === $len - 1) {
        if ($stmt instanceof IR\ExprStmt) {
          $pending_block->push_stmt(new PHP\AssignStmt($var, $this->expr($stmt->expr)));
        } else {
          throw new \Exception('last statement in block was expected to be IR\ExprStmt, found ' . get_class($stmt));
        }
      } else {
        $pending_block->push_stmt($this->stmt($stmt));
      }
    }
    array_pop($this->pending_blocks);
    return new PHP\BlockNode($pending_block->stmts);
  }

  private function block_with_return(IR\BlockNode $block): PHP\BlockNode {
    if ($block->length() === 0) {
      throw new \Exception('cannot return from empty block');
    }

    $pending_block = new PendingBlock($block->scope);
    array_push($this->pending_blocks, $pending_block);
    for ($i = 0, $len = $block->length(); $i < $len; $i++) {
      $stmt = $block->stmts[$i];
      if ($i === $len - 1) {
        if ($stmt instanceof IR\ExprStmt) {
          $pending_block->push_stmt(new PHP\ReturnStmt($this->expr($stmt->expr)));
        } else {
          throw new \Exception('last statement in block was expected to be IR\ExprStmt, found ' . get_class($stmt));
        }
      } else {
        $pending_block->push_stmt($this->stmt($stmt));
      }
    }
    array_pop($this->pending_blocks);
    return new PHP\BlockNode($pending_block->stmts);
  }

  private function peek_block(): PendingBlock {
    return $this->pending_blocks[count($this->pending_blocks) - 1];
  }

  private function stmt(IR\Stmt $stmt): PHP\Stmt {
    switch (true) {
      case $stmt instanceof IR\AssignStmt:
        return self::assign_stmt($stmt);
      case $stmt instanceof IR\ExprStmt:
        return self::expr_stmt($stmt);
    }
  }

  private function assign_stmt(IR\AssignStmt $stmt): PHP\AssignStmt {
    $expr = $this->expr($stmt->expr);
    $var = $this->current_scope()->new_variable($stmt->symbol, $stmt->name);
    return new PHP\AssignStmt($var, $expr);
  }

  private function expr_stmt(IR\ExprStmt $stmt): PHP\Stmt {
    switch (true) {
      case $stmt->expr instanceof IR\IfExpr:
        return $this->if_stmt($stmt->expr);
      default:
        return new PHP\ExprStmt($this->expr($stmt->expr));
    }
  }

  private function if_stmt(IR\IfExpr $expr): PHP\Stmt {
    $cond = $this->expr($expr->condition);
    $if_block = $this->block($expr->if_block);
    $else_block = $expr->else_block ? $this->block($expr->else_block) : null;
    return new PHP\IfStmt($cond, $if_block, $else_block);
  }

  private function expr(IR\Expr $expr): PHP\Expr {
    switch (true) {
      case $expr instanceof IR\FuncExpr:
        return $this->func_expr($expr);
      case $expr instanceof IR\IfExpr:
        return $this->if_expr($expr);
      case $expr instanceof IR\BinaryExpr:
        return $this->binary_expr($expr);
      case $expr instanceof IR\VariableExpr:
        return $this->var_expr($expr);
      case $expr instanceof IR\StrExpr:
        return $this->str_expr($expr);
      case $expr instanceof IR\NumExpr:
        return $this->num_expr($expr);
      default:
        throw new \Exception('cannot codegen expr ' . get_class($expr));
    }
  }

  private function func_expr(IR\FuncExpr $expr): PHP\Expr {
    $func_scope = new PHP\FuncScope($this->current_scope());
    $params = [];
    foreach ($expr->params as $param) {
      $var = $func_scope->new_variable($param->symbol, $param->name);
      $params[] = new PHP\VarExpr($var);
    }
    $this->push_scope($func_scope);
    if ($expr->type()->returns_something()) {
      $block = $this->block_with_return($expr->block);
    } else {
      $block = $this->block($expr->block);
    }
    $this->pop_scope();
    return new PHP\FuncExpr($params, $block);
  }

  private function if_expr(IR\IfExpr $expr): PHP\Expr {
    $block = $this->peek_block();
    $var = $this->current_scope()->new_temporary();
    $cond = $this->expr($expr->condition);
    $if_block = $this->block_with_assignment($expr->if_block, $var);
    $else_block = $this->block_with_assignment($expr->else_block, $var);
    $stmt = new PHP\IfStmt($cond, $if_block, $else_block);
    $block->push_stmt($stmt);
    return new PHP\VarExpr($var);
  }

  private function binary_expr(IR\BinaryExpr $expr): PHP\BinaryExpr {
    $left = $this->expr($expr->left);
    $right = $this->expr($expr->right);
    return new PHP\BinaryExpr($expr->operator, $left, $right);
  }

  private function var_expr(IR\VariableExpr $expr): PHP\VarExpr {
    $var = $this->current_scope()->get_variable($expr->symbol);
    return new PHP\VarExpr($var);
  }

  private function str_expr(IR\StrExpr $expr): PHP\StrExpr {
    return new PHP\StrExpr($expr->value);
  }

  private function num_expr(IR\NumExpr $expr): PHP\NumExpr {
    return new PHP\NumExpr($expr->value);
  }
}
