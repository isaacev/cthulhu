<?php

namespace Cthulhu\Types;

use Cthulhu\Parser\AST;

class Checker {
  public static function check_stmts(array $stmts, ?Binding $binding): Context {
    $context = new Context($binding, new VoidType());
    foreach ($stmts as $stmt) {
      $context = Checker::check_stmt($stmt, $context->binding);
    }
    return $context;
  }

  public static function check_stmt(AST\Stmt $stmt, ?Binding $binding): Context {
    switch (true) {
      case $stmt instanceof AST\LetStmt:
        return Checker::check_let_stmt($stmt, $binding);
      case $stmt instanceof AST\ExprStmt:
        return Checker::check_expr_stmt($stmt, $binding);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception('cannot check statement: ' . get_class($stmt));
        // @codeCoverageIgnoreEnd
    }
  }

  private static function check_let_stmt(AST\LetStmt $stmt, ?Binding $binding): Context {
    $expr_type = Checker::check_expr($stmt->expr, $binding);
    $binding = new Binding($binding, $stmt->name, $expr_type);
    $return_type = new VoidType();
    return new Context($binding, $return_type);
  }

  private static function check_expr_stmt(AST\ExprStmt $stmt, ?Binding $binding): Context {
    $return_type = Checker::check_expr($stmt->expr, $binding);
    return new Context($binding, $return_type);
  }

  public static function check_expr(AST\Expr $expr, ?Binding $binding): Type {
    switch (true) {
      case $expr instanceof AST\NumExpr:
        return new NumType();
      case $expr instanceof AST\StrExpr:
        return new StrType();
      case $expr instanceof AST\IdentExpr:
        return Checker::check_identifier_expr($expr, $binding);
      case $expr instanceof AST\BinaryExpr:
        return Checker::check_binary_expr($expr, $binding);
      case $expr instanceof AST\IfExpr:
        return Checker::check_if_expr($expr, $binding);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception('cannot check expression: ' . get_class($expr));
        // @codeCoverageIgnoreEnd
    }
  }

  public static function check_identifier_expr(AST\IdentExpr $expr, ?Binding $binding): Type {
    $resolved = $binding ? $binding->resolve($expr->name) : null;
    if ($resolved === null) {
      throw new Errors\UndeclaredVariable($expr->name);
    } else {
      return $resolved;
    }
  }

  public static function check_binary_expr(AST\BinaryExpr $expr, ?Binding $binding): Type {
    $left = Checker::check_expr($expr->left, $binding);
    $right = Checker::check_expr($expr->right, $binding);

    switch ($expr->operator) {
      case '+':
      case '-':
      case '*':
      case '/':
        if (($left instanceof NumType) === false) {
          throw new Errors\TypeMismatch(new NumType(), $left);
        } else if (($right instanceof NumType) === false) {
          throw new Errors\TypeMismatch(new NumType(), $right);
        } else {
          return new NumType();
        }
      case '<':
      case '<=':
      case '>':
      case '>=':
        if (($left instanceof NumType) === false) {
          throw new Errors\TypeMismatch(new NumType(), $left);
        } else if (($right instanceof NumType) === false) {
          throw new Errors\TypeMismatch(new NumType(), $right);
        } else {
          return new BoolType();
        }
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception("unknown operator: '$expr->operator'");
        // @codeCoverageIgnoreEnd
    }
  }

  public static function check_if_expr(AST\IfExpr $expr, ?Binding $binding): Type {
    $condition_type = Checker::check_expr($expr->condition, $binding);
    if (($condition_type instanceof BoolType) === false) {
      throw new Errors\TypeMismatch(new BoolType(), $condition_type);
    }

    $if_clause_type = Checker::check_stmts($expr->if_clause, $binding)->return_type;

    if ($expr->else_clause === null) {
      if ($if_clause_type instanceof VoidType) {
        return $if_clause_type;
      }

      throw new Errors\TypeMismatch(new VoidType(), $if_clause_type);
    }

    $else_clause_type = Checker::check_stmts($expr->else_clause, $binding)->return_type;

    if ($if_clause_type->accepts($else_clause_type)) {
      return $if_clause_type;
    } else {
      throw new Errors\TypeMismatch($if_clause_type, $else_clause_type);
    }
  }
}
