<?php

namespace Cthulhu\Codegen;

use Cthulhu\IR;

class Codegen {
  public static function generate(IR\SourceModule $module, PHP\Reference $main_fn): PHP\Program {
    $ctx = new Context();

    $builtins = array_map(function ($native_module) {
      return new PHP\Builtin($native_module->build());
    }, $module->builtins);

    $ctx->push_namespace(PHP\Reference::from_symbol($module->scope->symbol));
    self::items($ctx, $module->items);
    $ctx->pop_namespace();

    return new PHP\Program($builtins, $ctx->namespaces, $main_fn);
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
    $fn_name = new PHP\Reference([ $item->symbol->name ]);

    $params = [];
    foreach ($item->param_symbols as $param_symbol) {
      $params[] = new PHP\Variable($param_symbol->name);
    }

    $body = self::block($ctx, $item->body);
    $ctx->push_stmt_to_namespace(new PHP\FuncStmt($fn_name, $params, $body));
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
    $variable = new PHP\Variable($stmt->symbol->name);
    $expr = self::expr($ctx, $stmt->expr);
    $ctx->push_stmt_to_block(new PHP\AssignStmt($variable, $expr));
  }

  private static function return_stmt(Context $ctx, IR\ReturnStmt $stmt): void {
    $expr = self::expr($ctx, $stmt->expr);
    $ctx->push_stmt_to_block(new PHP\ReturnStmt($expr));
  }

  private static function semi_stmt(Context $ctx, IR\SemiStmt $stmt): void {
    $ctx->push_stmt_to_block(new PHP\SemiStmt(self::expr($ctx, $stmt->expr)));
  }

  private static function expr(Context $ctx, IR\Expr $expr): PHP\Expr {
    switch (true) {
      case $expr instanceof IR\IfExpr:
        return self::if_expr($ctx, $expr);
      case $expr instanceof IR\CallExpr:
        return self::call_expr($ctx, $expr);
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

  private static function if_expr(Context $ctx, IR\IfExpr $expr): PHP\Expr {
    $variable = new PHP\Variable('var' . rand(0, 1000));
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

  private static function reference_expr(Context $ctx, IR\ReferenceExpr $expr): PHP\Expr {
    if ($expr->symbol->parent === null) {
      $variable = new PHP\Variable($expr->symbol->name);
      return new PHP\VariableExpr($variable);
    } else {
      $reference = PHP\Reference::from_symbol($expr->symbol);
      return new PHP\ReferenceExpr($reference);
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
