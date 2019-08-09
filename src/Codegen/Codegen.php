<?php

namespace Cthulhu\Codegen;

use Cthulhu\AST;

class Codegen {
  public static function generate(AST\Root $root): string {
    $writer = new Writer();
    $php = Codegen::root($root);
    return $php->write(new Writer())->collect();
  }

  public static function root(AST\Root $root): PHP\Root {
    return new PHP\Root(Codegen::stmts($root->stmts));
  }

  public static function stmts(array $stmts): array {
    return array_map(function ($stmt) {
      return Codegen::stmt($stmt);
    }, $stmts);
  }

  public static function stmt(AST\Stmt $stmt): PHP\Stmt {
    switch (true) {
      case $stmt instanceof AST\LetStmt:
        return Codegen::let_stmt($stmt);
      case $stmt instanceof AST\ExprStmt:
        return Codegen::expr_stmt($stmt);
      default:
        throw new \Exception('unknown statement: ' . get_class($stmt));
    }
  }

  private static function let_stmt(AST\LetStmt $stmt): PHP\AssignStmt {
    return new PHP\AssignStmt($stmt->name, Codegen::expr($stmt->expr));
  }

  private static function expr_stmt(AST\ExprStmt $stmt): PHP\ExprStmt {
    return new PHP\ExprStmt(Codegen::expr($stmt->expr));
  }

  public static function expr(AST\Expr $expr): PHP\Expr {
    switch (true) {
      case $expr instanceof AST\BinaryExpr:
        return Codegen::binary_expr($expr);
      case $expr instanceof AST\IdentExpr:
        return Codegen::ident_expr($expr);
      case $expr instanceof AST\StrExpr:
        return Codegen::str_expr($expr);
      case $expr instanceof AST\NumExpr:
        return Codegen::num_expr($expr);
      default:
        throw new \Exception('unknown expression: ' . get_class($expr));
    }
  }

  private static function binary_expr(AST\BinaryExpr $expr): PHP\BinaryExpr {
    return new PHP\BinaryExpr($expr->operator, Codegen::expr($expr->left), Codegen::expr($expr->right));
  }

  private static function ident_expr(AST\IdentExpr $expr): PHP\IdentExpr {
    return new PHP\IdentExpr($expr->name);
  }

  private static function str_expr(AST\StrExpr $expr): PHP\StrExpr {
    return new PHP\StrExpr($expr->value);
  }

  private static function num_expr(AST\NumExpr $expr): PHP\NumExpr {
    return new PHP\NumExpr($expr->value);
  }
}
