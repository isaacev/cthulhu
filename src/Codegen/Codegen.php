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
    $name = new PHP\Reference([ $item->symbol->name ]);
    $params = []; // TODO
    $body = self::block($ctx, $item->body);
    $ctx->push_stmt_to_namespace(new PHP\FuncStmt($name, $params, $body));
  }

  private static function block(Context $ctx, IR\BlockNode $block): PHP\BlockNode {
    $ctx->push_block();
    foreach ($block->stmts as $stmt) {
      self::stmt($ctx, $stmt);
    }
    return $ctx->pop_block();
  }

  private static function stmt(Context $ctx, IR\Stmt $stmt): void {
    switch (true) {
      case $stmt instanceof IR\AssignStmt:
        self::assign_stmt($ctx, $stmt);
        break;
      case $stmt instanceof IR\ExprStmt:
        self::expr_stmt($ctx, $stmt);
        break;
      default:
        throw new \Exception('unknown statement');
    }
  }

  private static function assign_stmt(Context $ctx, IR\AssignStmt $stmt): void {
    $variable = new PHP\Variable($stmt->symbol->name);
    $expr = self::expr($ctx, $stmt->expr);
    $ctx->push_stmt_to_block(new PHP\AssignStmt($variable, $expr));
  }

  private static function expr_stmt(Context $ctx, IR\ExprStmt $stmt): void {
    $ctx->push_stmt_to_block(new PHP\ExprStmt(self::expr($ctx, $stmt->expr)));
  }

  private static function expr(Context $ctx, IR\Expr $expr): PHP\Expr {
    switch (true) {
      case $expr instanceof IR\CallExpr:
        return self::call_expr($ctx, $expr);
      case $expr instanceof IR\ReferenceExpr:
        return self::reference_expr($ctx, $expr);
      case $expr instanceof IR\StrExpr:
        return self::str_expr($ctx, $expr);
      case $expr instanceof IR\NumExpr:
        return self::num_expr($ctx, $expr);
      default:
        throw new \Exception('unknown expression');
    }
  }

  private static function call_expr(Context $ctx, IR\CallExpr $expr): PHP\CallExpr {
    $callee = self::expr($ctx, $expr->callee);
    $args = array_map(function ($arg) use ($ctx) {
      return self::expr($ctx, $arg);
    }, $expr->args);
    return new PHP\CallExpr($callee, $args);
  }

  private static function reference_expr(Context $ctx, IR\ReferenceExpr $expr): PHP\ReferenceExpr {
    $reference = PHP\Reference::from_symbol($expr->symbol);
    return new PHP\ReferenceExpr($reference);
  }

  private static function str_expr(Context $ctx, IR\StrExpr $expr): PHP\StrExpr {
    return new PHP\StrExpr($expr->value);
  }

  private static function num_expr(Context $ctx, IR\NumExpr $expr): PHP\NumExpr {
    return new PHP\NumExpr($expr->value);
  }
}
