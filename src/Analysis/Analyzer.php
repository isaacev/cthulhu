<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\IR;
use Cthulhu\Types;

/**
 * Analyzer takes an abstract syntax tree (AST) and returns a type-annotated
 * intermediate representation (IR). In addition to type annotations, the IR
 * contains information about which variables are declared in each scope and
 * allows for easier generation of PHP code.
 */
class Analyzer {
  public static function analyze(AST\Root $root): IR\RootNode {
    return self::root_node($root);
  }

  private static function root_node(AST\Root $root): IR\RootNode {
    $global_scope = new IR\GlobalScope(null);
    $block_scope = new IR\BlockScope($global_scope);
    $stmts = [];
    foreach ($root->stmts as $stmt) {
      $stmts[] = self::stmt($block_scope, $stmt);
    }
    $block = new IR\BlockNode($block_scope, $stmts);
    return new IR\RootNode($global_scope, $block);
  }

  private static function block_node(IR\Scope $scope, AST\BlockNode $block): IR\BlockNode {
    $block_scope = new IR\BlockScope($scope);
    $stmts = [];
    foreach ($block->stmts as $stmt) {
      $stmts[] = self::stmt($block_scope, $stmt);
    }
    return new IR\BlockNode($block_scope, $stmts);
  }

  private static function stmt(IR\Scope $scope, AST\Stmt $stmt): IR\Stmt {
    switch (true) {
      case $stmt instanceof AST\LetStmt:
        return self::let_stmt($scope, $stmt);
      case $stmt instanceof AST\ExprStmt:
        return self::expr_stmt($scope, $stmt);
      default:
        throw new \Exception('unknown statement: ' . get_class($stmt));
    }
  }

  private static function let_stmt(IR\Scope $scope, AST\LetStmt $stmt): IR\AssignStmt {
    $name = $stmt->name;
    $expr = self::expr($scope, $stmt->expr);
    $symbol = $scope->new_binding($name, $expr->type());
    return new IR\AssignStmt($name, $symbol, $expr);
  }

  private static function expr_stmt(IR\Scope $scope, AST\ExprStmt $stmt): IR\ExprStmt {
    $expr = self::expr($scope, $stmt->expr);
    return new IR\ExprStmt($expr);
  }

  private static function expr(IR\Scope $scope, AST\Expr $expr): IR\Expr {
    switch (true) {
      case $expr instanceof AST\FuncExpr:
        return self::func_expr($scope, $expr);
      case $expr instanceof AST\IfExpr:
        return self::if_expr($scope, $expr);
      case $expr instanceof AST\CallExpr:
        return self::call_expr($scope, $expr);
      case $expr instanceof AST\BinaryExpr:
        return self::binary_expr($scope, $expr);
      case $expr instanceof AST\IdentExpr:
        return self::ident_expr($scope, $expr);
      case $expr instanceof AST\StrExpr:
        return self::str_expr($scope, $expr);
      case $expr instanceof AST\NumExpr:
        return self::num_expr($scope, $expr);
      default:
        throw new \Exception('unknown expression: ' . get_class($expr));
    }
  }

  private static function func_expr(IR\Scope $scope, AST\FuncExpr $expr): IR\FuncExpr {
    $func_scope = new IR\FuncScope($scope);
    $params = array_map(function ($pair) use ($func_scope) {
      $name = $pair['name'];
      $type = self::annotation_to_type($pair['annotation']);
      $symbol = $func_scope->new_binding($name, $type);
      return new IR\ParamNode($name, $symbol);
    }, $expr->params);
    $return_type = self::annotation_to_type($expr->return_annotation);
    $block = self::block_node($func_scope, $expr->block);
    if ($return_type->accepts($block->type()) === false) {
      throw new Types\Errors\TypeMismatch($return_type, $block->type());
    }
    return new IR\FuncExpr($params, $return_type, $block);
  }

  private static function if_expr(IR\Scope $scope, AST\IfExpr $expr): IR\IfExpr {
    $condition = self::expr($scope, $expr->condition);
    if (($condition->type() instanceof Types\BoolType) === false) {
      throw new Types\Errors\TypeMismatch(new Types\BoolType(), $condition->type());
    }

    $if_block = self::block_node($scope, $expr->if_clause);
    $else_block = $expr->else_clause ? self::block_node($scope, $expr->else_clause) : null;

    if ($expr->else_clause === null) {
      // If there isn't an else-clause, require that
      // the return type of the if-clause be Void.
      if (($if_block->type() instanceof Types\VoidType) === false) {
        throw new Types\Errors\TypeMismatch(new Types\VoidType(), $if_block->type());
      }

      return new IR\IfExpr(new Types\VoidType(), $condition, $if_block, null);
    }

    // If there is an else-clause, require that the if-clause and the
    // else-clause have an equivalent return type.
    if ($if_block->type()->accepts($else_block->type())) {
      return new IR\IfExpr($if_block->type(), $condition, $if_block, $else_block);
    }

    throw new Types\Errors\TypeMismatch($if_block->type(), $else_block->type());
  }

  private static function call_expr(IR\Scope $scope, AST\CallExpr $expr): IR\CallExpr {
    $callee = self::expr($scope, $expr->callee);
    if (($callee->type() instanceof Types\FuncType) === false) {
      throw new Types\Errors\TypeMismatch('function', $callee->type());
    }
    $args = [];
    foreach ($expr->args as $arg) {
      $args[] = self::expr($scope, $arg);
    }
    return new IR\CallExpr($callee, $args);
  }

  private static function binary_expr(IR\Scope $scope, AST\BinaryExpr $expr): IR\BinaryExpr {
    $left = self::expr($scope, $expr->left);
    $right = self::expr($scope, $expr->right);
    $type = $left->type()->binary_operator($expr->operator, $right->type());
    return new IR\BinaryExpr($type, $expr->operator, $left, $right);
  }

  private static function ident_expr(IR\Scope $scope, AST\IdentExpr $expr): IR\VariableExpr {
    $name = $expr->name;
    $symbol = $scope->get_binding($name);
    return new IR\VariableExpr($name, $symbol);
  }

  private static function str_expr(IR\Scope $scope, AST\StrExpr $expr): IR\StrExpr {
    return new IR\StrExpr($expr->value);
  }

  private static function num_expr(IR\Scope $scope, AST\NumExpr $expr): IR\NumExpr {
    return new IR\NumExpr($expr->value);
  }

  private static function annotation_to_type(AST\Annotation $annotation): Types\Type {
    if ($annotation instanceof AST\NamedAnnotation) {
      switch ($annotation->name) {
        case 'Void':
          return new Types\VoidType();
        case 'Num':
          return new Types\NumType();
        case 'Str':
          return new Types\StrType();
        case 'Bool':
          return new Types\BoolType();
        default:
          throw new Types\Errors\UnknownType($annotation->name);
      }
    }

    throw new Types\Errors\UnknownType("$annotation");
  }
}
