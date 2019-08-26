<?php

namespace Cthulhu\Codegen;

use Cthulhu\IR;

class Codegen {
  public static function generate(IR\SourceModule $module): PHP\File {
    $cg = new Codegen();
    // build any native modules here
    $cg->module_root($module);
    return new PHP\File($cg->namespaces);
  }

  private $namespaces;
  private $pending_blocks;
  private $pending_scopes;
  private $rename_table;

  private function __construct() {
    $this->namespaces = [];
    $this->pending_blocks = [];
    $this->pending_scopes = [];
    $this->rename_table = [
      '3' => 'xxx'
    ];
  }

  private function rename(IR\Symbol $symbol, string $rename) {
    $this->rename_table[$symbol->id] = $rename;
  }

  private function check_for_rename(IR\IdentifierNode $ident): string {
    if (array_key_exists($ident->symbol->id, $this->rename_table)) {
      return $this->rename_table[$ident->symbol->id];
    } else {
      return $ident->name;
    }
  }

  private function peek_scope(): PHP\Scope2 {
    return $this->pending_scopes[count($this->pending_scopes) - 1];
  }

  private function push_scope(PHP\Scope2 $scope): void {
    array_push($this->pending_scopes, $scope);
  }

  private function pop_scope(): PHP\Scope2 {
    return array_pop($this->pending_scopes);
  }

  private function module_root(IR\SourceModule $module): void {
    $this->push_scope(new PHP\NamespaceScope(null));
    $path = $this->module_path($module->scope);
    $block = $this->block($module->block);
    $this->pop_scope();
    $this->namespaces[] = new PHP\NamespaceNode($path, $block);
  }

  private function module_stmt(IR\ModuleStmt $module): void {
    $this->push_scope(new PHP\NamespaceScope($this->peek_scope()));
    $path = $this->module_path($module->scope);
    $block = $this->block($module->block);
    $this->pop_scope();
    $this->namespaces[] = new PHP\NamespaceNode($path, $block);
  }

  private function module_path(IR\ModuleScope $module_scope): PHP\IdentifierPath {
    $segments = array_map(function ($ident) {
      return new PHP\Identifier($this->check_for_rename($ident));
    }, $module_scope->get_path_segments());
    return new PHP\IdentifierPath($segments);
  }

  private function block(IR\BlockNode $block): PHP\BlockNode {
    $pending_block = new PendingBlock($block->scope);
    array_push($this->pending_blocks, $pending_block);
    foreach ($block->stmts as $stmt) {
      $stmt = $this->stmt($stmt);
      if ($stmt) {
        $pending_block->push_stmt($stmt);
      }
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
          $assignee = new PHP\VariableExpr($var);
          $pending_block->push_stmt(new PHP\AssignStmt($assignee, $this->expr($stmt->expr)));
        } else {
          throw new \Exception('last statement in block was expected to be IR\ExprStmt, found ' . get_class($stmt));
        }
      } else {
        $stmt = $this->stmt($stmt);
        if ($stmt) {
          $pending_block->push_stmt($stmt);
        }
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
        $stmt = $this->stmt($stmt);
        if ($stmt) {
          $pending_block->push_stmt($stmt);
        }
      }
    }
    array_pop($this->pending_blocks);
    return new PHP\BlockNode($pending_block->stmts);
  }

  private function peek_block(): PendingBlock {
    return $this->pending_blocks[count($this->pending_blocks) - 1];
  }

  private function stmt(IR\Stmt $stmt): ?PHP\Stmt {
    switch (true) {
      case $stmt instanceof IR\ModuleStmt:
        return $this->module_stmt($stmt);
      case $stmt instanceof IR\AssignStmt:
        return $this->assign_stmt($stmt);
      case $stmt instanceof IR\ExprStmt:
        return $this->expr_stmt($stmt);
      default:
        throw new \Exception('cannot generate code for ' . get_class($stmt));
    }
  }

  private function assign_stmt(IR\AssignStmt $stmt): PHP\Stmt {
    if ($stmt->expr instanceof IR\FuncExpr) {
      $name = new PHP\Identifier($this->check_for_rename($stmt->identifier));
      return $this->func_stmt($name, $stmt->expr);
    }

    $expr = $this->expr($stmt->expr);
    $assignee = new PHP\Variable($this->check_for_rename($stmt->identifier));
    return new PHP\AssignStmt($assignee, $expr);
  }

  private function func_stmt(PHP\Identifier $name, IR\FuncExpr $expr): PHP\FuncStmt {
    $this->push_scope(new PHP\FunctionScope($this->peek_scope()));
    $this->peek_scope()->set_params($expr->params);
    $block = $expr->type()->returns_something()
      ? $this->block_with_return($expr->block)
      : $this->block($expr->block);
    $scope = $this->pop_scope();
    return new PHP\FuncStmt($name, $scope->get_params(), $block);
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
      case $expr instanceof IR\CallExpr:
        return $this->call_expr($expr);
      case $expr instanceof IR\BinaryExpr:
        return $this->binary_expr($expr);
      case $expr instanceof IR\PathExpr:
        return $this->path_expr($expr);
      case $expr instanceof IR\StrExpr:
        return $this->str_expr($expr);
      case $expr instanceof IR\NumExpr:
        return $this->num_expr($expr);
      default:
        throw new \Exception('cannot codegen expr ' . get_class($expr));
    }
  }

  private function func_expr(IR\FuncExpr $expr): PHP\FuncExpr {
    $this->push_scope(new PHP\FunctionScope($this->peek_scope()));
    $this->peek_scope()->set_params($expr->params);
    $block = $expr->type()->returns_something()
      ? $this->block_with_return($expr->block)
      : $this->block($expr->block);
    $scope = $this->pop_scope();
    return new PHP\FuncExpr($scope->get_params(), $scope->get_free_variables(), $block);
  }

  private function if_expr(IR\IfExpr $expr): PHP\Expr {
    $block = $this->peek_block();
    $var = $this->peek_scope()->new_temporary();
    $cond = $this->expr($expr->condition);
    $if_block = $this->block_with_assignment($expr->if_block, $var);
    $else_block = $this->block_with_assignment($expr->else_block, $var);
    $stmt = new PHP\IfStmt($cond, $if_block, $else_block);
    $block->push_stmt($stmt);
    return new PHP\VarExpr($var);
  }

  private function call_expr(IR\CallExpr $expr): PHP\Expr {
    $callee = $this->expr($expr->callee);
    $args = array_map(function ($arg) { return $this->expr($arg); }, $expr->args);
    return new PHP\CallExpr($callee, $args);
  }

  private function binary_expr(IR\BinaryExpr $expr): PHP\BinaryExpr {
    $left = $this->expr($expr->left);
    $right = $this->expr($expr->right);
    return new PHP\BinaryExpr($expr->operator, $left, $right);
  }

  private function path_expr(IR\PathExpr $expr): PHP\Expr {
    $segments = array_map(function ($ident) {
      return $this->check_for_rename($ident);
    }, $expr->segments);

    if (count($segments) === 1) {
      return new PHP\VariableExpr($segments[0]);
    } else {
      return new PHP\ReferenceExpr($segments);
    }
  }

  private function str_expr(IR\StrExpr $expr): PHP\StrExpr {
    return new PHP\StrExpr($expr->value);
  }

  private function num_expr(IR\NumExpr $expr): PHP\NumExpr {
    return new PHP\NumExpr($expr->value);
  }
}
