<?php

namespace Cthulhu\Codegen;

use Cthulhu\IR;
use Cthulhu\Types;

class Codegen {
  public static function generate(IR\Program $prog): PHP\Program {
    $ctx = new Context();

    $builtin_namespaces = [];
    foreach ($prog->root_module->builtins as $native_module) {
      $builtin_namespaces[] = $native_module->codegen($ctx->renamer);
    }

    $ctx->push_namespace($ctx->renamer->get_reference($prog->root_module->scope->symbol));
    $ctx->renamer->push_scope(new PHP\NamespaceScope($ctx->renamer->current_scope()));
    self::items($ctx, $prog->root_module->items);
    $main_ref = $ctx->renamer->get_reference($prog->entry_point);
    $ctx->pop_namespace();
    $ctx->renamer->pop_scope();

    $namespaces = array_merge($builtin_namespaces, $ctx->namespaces);
    $namespaces[] = new PHP\NamespaceNode(null, new PHP\BlockNode([
      new PHP\SemiStmt(
        new PHP\CallExpr(
          new PHP\ReferenceExpr($main_ref),
          []
        )
      )
    ]));

    return new PHP\Program($namespaces);
  }

  private static function items(Context $ctx, array $items): void {
    foreach ($items as $item) {
      self::item($ctx, $item);
    }
  }

  private static function item(Context $ctx, IR\Item $item): void {
    switch (true) {
      case $item instanceof IR\UseItem:
        self::use_item($ctx, $item);
        break;
      case $item instanceof IR\ModItem:
        self::use_item($ctx, $item);
        break;
      case $item instanceof IR\FnItem:
        self::fn_item($ctx, $item);
        break;
      default:
        throw new \Exception('unknown item');
    }
  }

  private static function use_item(Context $ctx, IR\UseItem $item): void {
    // TODO
  }

  private static function mod_item(Context $ctx, IR\ModItem $item): void {
    // TODO
  }

  private static function fn_item(Context $ctx, IR\FnItem $item): void {
    $fn_name = new PHP\Reference($item->symbol, [ $ctx->renamer->resolve($item->symbol) ]);

    $ctx->renamer->push_scope(new PHP\FunctionScope($ctx->renamer->current_scope()));
    $params = [];
    foreach ($item->param_symbols as $param_symbol) {
      $params[] = $ctx->renamer->get_variable($param_symbol);
    }

    $body = self::block($ctx, $item->body);
    $ctx->renamer->pop_scope();
    $ctx->push_stmt_to_namespace(new PHP\FuncStmt($fn_name, $params, $body, $item->attrs));
  }

  private static function block(Context $ctx, IR\BlockNode $block): PHP\BlockNode {
    $ctx->push_block();
    foreach ($block->stmts as $stmt) {
      self::stmt($ctx, $stmt);
    }
    return $ctx->pop_block();
  }

  private static function block_with_trailing_assignment(Context $ctx, IR\BlockNode $block, PHP\Variable $var): PHP\BlockNode {
    $ctx->push_block();
    foreach ($block->stmts as $index => $stmt) {
      if ($index === $block->length() - 1) {
        if ($stmt instanceof IR\ReturnStmt) {
          $expr = self::expr($ctx, $stmt->expr);
          $ctx->push_stmt_to_block(new PHP\AssignStmt($var, $expr));
        } else {
          self::stmt($ctx, $stmt);
          $expr = new PHP\NullLiteral();
          $ctx->push_stmt_to_block(new PHP\AssignStmt($var, $expr));
        }
      } else {
        self::stmt($ctx, $stmt);
      }
    }
    return $ctx->pop_block();
  }

  private static function stmt(Context $ctx, IR\Stmt $stmt): void {
    switch (true) {
      case $stmt instanceof IR\AssignStmt:
        self::assign_stmt($ctx, $stmt);
        break;
      case $stmt instanceof IR\ReturnStmt:
        self::return_stmt($ctx, $stmt);
        break;
      case $stmt instanceof IR\SemiStmt:
        self::semi_stmt($ctx, $stmt);
        break;
      default:
        throw new \Exception('unknown statement: ' . get_class($stmt));
    }
  }

  private static function assign_stmt(Context $ctx, IR\AssignStmt $stmt): void {
    $var = $ctx->renamer->get_variable($stmt->symbol);
    $expr = self::expr($ctx, $stmt->expr);
    $ctx->push_stmt_to_block(new PHP\AssignStmt($var, $expr));
  }

  private static function return_stmt(Context $ctx, IR\ReturnStmt $stmt): void {
    if ($stmt->expr->type() instanceof Types\VoidType) {
      if ($stmt->expr instanceof IR\IfExpr) {
        self::if_stmt($ctx, $stmt->expr);
      } else {
        $expr = self::expr($ctx, $stmt->expr);
        $ctx->push_stmt_to_block(new PHP\SemiStmt($expr));
      }
    } else {
      $expr = self::expr($ctx, $stmt->expr);
      $ctx->push_stmt_to_block(new PHP\ReturnStmt($expr));
    }
  }

  private static function semi_stmt(Context $ctx, IR\SemiStmt $stmt): void {
    if ($stmt->expr instanceof IR\IfExpr) {
      self::if_stmt($ctx, $stmt->expr);
    } else {
      $ctx->push_stmt_to_block(new PHP\SemiStmt(self::expr($ctx, $stmt->expr)));
    }
  }

  private static function expr(Context $ctx, IR\Expr $expr): PHP\Expr {
    switch (true) {
      case $expr instanceof IR\IfExpr:
        return self::if_expr($ctx, $expr);
      case $expr instanceof IR\CallExpr:
        return self::call_expr($ctx, $expr);
      case $expr instanceof IR\BinaryExpr:
        return self::binary_expr($ctx, $expr);
      case $expr instanceof IR\UnaryExpr:
        return self::unary_expr($ctx, $expr);
      case $expr instanceof IR\ReferenceExpr:
        return self::reference_expr($ctx, $expr);
      case $expr instanceof IR\StrExpr:
        return self::str_expr($ctx, $expr);
      case $expr instanceof IR\NumExpr:
        return self::num_expr($ctx, $expr);
      case $expr instanceof IR\BoolExpr:
        return self::bool_expr($ctx, $expr);
      default:
        throw new \Exception('unknown expression: ' . get_class($expr));
    }
  }

  private static function if_stmt(Context $ctx, IR\IfExpr $expr): void {
    $cond = self::expr($ctx, $expr->condition);
    $if_true = self::block($ctx, $expr->if_block);
    $if_false = $expr->else_block
      ? self::block($ctx, $expr->else_block)
      : null;
    $ctx->push_stmt_to_block(new PHP\IfStmt($cond, $if_true, $if_false));
  }

  private static function if_expr(Context $ctx, IR\IfExpr $expr): PHP\Expr {
    $variable = $ctx->renamer->allocate_variable('var');
    $cond = self::expr($ctx, $expr->condition);
    $if_true = self::block_with_trailing_assignment($ctx, $expr->if_block, $variable);
    $if_false = $expr->else_block
      ? self::block_with_trailing_assignment($ctx, $expr->else_block, $variable)
      : null;
    $ctx->push_stmt_to_block(new PHP\IfStmt($cond, $if_true, $if_false));
    return new PHP\VariableExpr($variable);
  }

  private static function call_expr(Context $ctx, IR\CallExpr $expr): PHP\CallExpr {
    $callee = self::expr($ctx, $expr->callee);
    $args = array_map(function ($arg) use ($ctx) {
      return self::expr($ctx, $arg);
    }, $expr->args);
    return new PHP\CallExpr($callee, $args);
  }

  private static function binary_expr(Context $ctx, IR\BinaryExpr $expr): PHP\BinaryExpr {
    $left = self::expr($ctx, $expr->left);
    $right = self::expr($ctx, $expr->right);
    return new PHP\BinaryExpr($expr->operator, $left, $right);
  }

  private static function unary_expr(Context $ctx, IR\UnaryExpr $expr): PHP\UnaryExpr {
    $operand = self::expr($ctx, $expr->operand);
    return new PHP\UnaryExpr($expr->operator, $operand);
  }

  private static function reference_expr(Context $ctx, IR\ReferenceExpr $expr): PHP\Expr {
    if ($expr->symbol->parent === null) {
      return new PHP\VariableExpr($ctx->renamer->get_variable($expr->symbol));
    } else {
      return new PHP\ReferenceExpr($ctx->renamer->get_reference($expr->symbol));
    }
  }

  private static function str_expr(Context $ctx, IR\StrExpr $expr): PHP\StrExpr {
    return new PHP\StrExpr($expr->value);
  }

  private static function num_expr(Context $ctx, IR\NumExpr $expr): PHP\NumExpr {
    return new PHP\NumExpr($expr->value);
  }

  private static function bool_expr(Context $ctx, IR\BoolExpr $expr): PHP\BoolExpr {
    return new PHP\BoolExpr($expr->value);
  }
}
