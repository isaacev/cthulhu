<?php

namespace Cthulhu\Types;

use Cthulhu\Parser\AST;

class Checker {
  public static function check_block(AST\Block $block, ?Binding $binding): Context {
    $context = new Context($binding, new VoidType());
    foreach ($block->statements as $stmt) {
      $context = Checker::check_stmt($stmt, $context->binding);
    }
    return $context;
  }

  public static function check_stmt(AST\Statement $stmt, ?Binding $binding): Context {
    switch (true) {
      case $stmt instanceof AST\LetStatement:
        return Checker::check_let_stmt($stmt, $binding);
      case $stmt instanceof AST\ExpressionStatement:
        return Checker::check_expr_stmt($stmt, $binding);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception('cannot check statement: ' . get_class($stmt));
        // @codeCoverageIgnoreEnd
    }
  }

  private static function check_let_stmt(AST\LetStatement $stmt, ?Binding $binding): Context {
    $expr_type = Checker::check_expr($stmt->expression, $binding);
    $binding = new Binding($binding, $stmt->name, $expr_type);
    $return_type = new VoidType();
    return new Context($binding, $return_type);
  }

  private static function check_expr_stmt(AST\ExpressionStatement $stmt, ?Binding $binding): Context {
    $return_type = Checker::check_expr($stmt->expression, $binding);
    return new Context($binding, $return_type);
  }

  public static function check_expr(AST\Expression $expr, ?Binding $binding): Type {
    switch (true) {
      case $expr instanceof AST\NumLiteralExpression:
        return new NumType();
      case $expr instanceof AST\StrLiteralExpression:
        return new StrType();
      case $expr instanceof AST\Identifier:
        return Checker::check_identifier_expr($expr, $binding);
      case $expr instanceof AST\BinaryOperator:
        return Checker::check_binary_expr($expr, $binding);
      case $expr instanceof AST\IfExpression:
        return Checker::check_if_expr($expr, $binding);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception('cannot check expression: ' . get_class($expr));
        // @codeCoverageIgnoreEnd
    }
  }

  public static function check_identifier_expr(AST\Identifier $expr, ?Binding $binding): Type {
    $resolved = $binding ? $binding->resolve($expr->name) : null;
    if ($resolved === null) {
      throw new Errors\UndeclaredVariable($expr->name);
    } else {
      return $resolved;
    }
  }

  public static function check_binary_expr(AST\BinaryOperator $expr, ?Binding $binding): Type {
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

  public static function check_if_expr(AST\IfExpression $expr, ?Binding $binding): Type {
    $condition_type = Checker::check_expr($expr->condition, $binding);
    if (($condition_type instanceof BoolType) === false) {
      throw new Errors\TypeMismatch(new BoolType(), $condition_type);
    }

    $if_clause_type = Checker::check_block($expr->if_clause, $binding)->return_type;

    if ($expr->else_clause === null) {
      if ($if_clause_type instanceof VoidType) {
        return $if_clause_type;
      }

      throw new Errors\TypeMismatch(new VoidType(), $if_clause_type);
    }

    $else_clause_type = Checker::check_block($expr->else_clause, $binding)->return_type;

    if ($if_clause_type->accepts($else_clause_type)) {
      return $if_clause_type;
    } else {
      throw new Errors\TypeMismatch($if_clause_type, $else_clause_type);
    }
  }
}
