<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\IR;
use Cthulhu\Types;

class Analyzer {
  public static function file(string $filename, AST\File $file): IR\SourceModule {
    $ctx = new Context($filename);
    $items = [];
    foreach ($file->items as $item) {
      $items[] = self::item($ctx, $item);
    }
    return new IR\SourceModule($ctx->used_builtins, $ctx->pop_module_scope(), $items);
  }

  private static function item(Context $ctx, AST\Item $item): IR\Item {
    switch (true) {
      case $item instanceof AST\UseItem:
        return self::use_item($ctx, $item);
      case $item instanceof AST\ModItem:
        return self::mod_item($ctx, $item);
      case $item instanceof AST\FnItem:
        return self::fn_item($ctx, $item);
      default:
        throw new \Exception('illegal item in module');
    }
  }

  private static function use_item(Context $ctx, AST\UseItem $item): IR\UseItem {
    $name = $item->name->ident;
    $remote_scope = $ctx->resolve_module_scope($name);
    $ctx->current_module_scope()->add($remote_scope->symbol, $remote_scope);
    return new IR\UseItem($remote_scope->symbol);
  }

  private static function mod_item(Context $ctx, AST\ModItem $item): IR\ModItem {
    $name = $item->name->ident;
    $ctx->push_module_scope();
    $items = [];
    foreach ($item->items as $item) {
      $items[] = self::item($ctx, $item);
    }
    return new IR\ModItem($ctx->pop_module_scope(), $items);
  }

  private static function fn_item(Context $ctx, AST\FnItem $item): IR\FnItem {
    $name = $item->name->ident;
    $symbol = new IR\Symbol($name, $ctx->current_module_scope()->symbol);
    $type = new Types\FnType([], new Types\VoidType()); // TODO
    $ctx->current_module_scope()->add($symbol, $type);
    $ctx->push_block_scope();
    // TODO: check function body
    $body = self::block($ctx, $item->body);
    $wanted_type = $type->returns;
    $given_type = $body->type();
    if (($wanted_type->accepts($given_type)) === false) {
      throw new Types\Errors\TypeMismatch($wanted_type, $given_type);
    }
    return new IR\FnItem($symbol, $type, $ctx->pop_block_scope(), $body);
  }

  private static function block(Context $ctx, AST\BlockNode $block): IR\BlockNode {
    $ctx->push_block_scope();
    $stmts = [];
    foreach ($block->stmts as $stmt) {
      $stmts[] = self::stmt($ctx, $stmt);
    }
    return new IR\BlockNode($ctx->pop_block_scope(), $stmts);
  }

  private static function stmt(Context $ctx, AST\Stmt $stmt): IR\Stmt {
    switch (true) {
      case $stmt instanceof AST\LetStmt:
        return self::let_stmt($ctx, $stmt);
      case $stmt instanceof AST\ExprStmt:
        return self::expr_stmt($ctx, $stmt);
      default:
        throw new \Exception('illegal statement in block');
    }
  }

  // let_stmt

  private static function expr_stmt(Context $ctx, AST\ExprStmt $stmt): IR\Stmt {
    return new IR\ExprStmt(self::expr($ctx, $stmt->expr));
  }

  private static function expr(Context $ctx, AST\Expr $expr): IR\Expr {
    switch (true) {
      case $expr instanceof AST\CallExpr:
        return self::call_expr($ctx, $expr);
      case $expr instanceof AST\PathExpr:
        return self::path_expr($ctx, $expr);
      case $expr instanceof AST\StrExpr:
        return self::str_expr($ctx, $expr);
      case $expr instanceof AST\NumExpr:
        return self::num_expr($ctx, $expr);
    }
  }

  // fn_expr
  // if_expr

  private static function call_expr(Context $ctx, AST\CallExpr $expr): IR\CallExpr {
    $callee = self::expr($ctx, $expr->callee);
    if (($callee->type() instanceof Types\FnType) === false) {
      throw new Types\Errors\TypeMismatch('function', $callee->type());
    }
    $args = [];
    $wanted_num_args = count($callee->type()->params);
    $given_num_args = count($expr->args);
    if ($wanted_num_args !== $given_num_args) {
      throw new \Exception("wanted $wanted_num_args arguments, was given $given_num_args");
    } else {
      $args = [];
      for ($i = 0; $i < $wanted_num_args; $i++) {
        $wanted_type = $callee->type()->params[$i];
        $args[] = $given_expr = self::expr($ctx, $expr->args[$i]);
        if (($wanted_type->accepts($given_expr->type())) === false) {
          throw new Types\Errors\TypeMismatch($wanted_type, $given_expr->type());
        }
      }
      return new IR\CallExpr($callee, $args);
    }
  }

  // binary_expr
  // unary_expr

  private static function path_expr(Context $ctx, AST\PathExpr $expr): IR\ReferenceExpr {
    if ($expr->length() === 1) {
      // Treat path as a reference to a name within the local scope
      $name = $expr->nth(0)->ident;
      $symbol = $ctx->current_block_scope()->to_symbol($name);
      $type = $ctx->current_block_scope()->lookup($symbol);
      return new IR\ReferenceExpr($symbol, $type);
    } else {
      // Treat path as a reference to a name within a module
      $module_segments = array_slice($expr->segments, 0, -1);
      $module = array_reduce($module_segments, function ($module, $segment) {
        $symbol = $module->to_symbol($segment->ident);
        $module_or_type = $module->lookup($symbol);
        if (($module_or_type instanceof IR\ModuleScope) === false) {
          throw new \Exception("$symbol has type $module_or_type but was referenced as a module");
        } else {
          return $module_or_type;
        }
      }, $ctx->current_module_scope());
      $last_segment = end($expr->segments);
      $symbol = $module->to_symbol($last_segment->ident);
      $type = $module->lookup($symbol);
      return new IR\ReferenceExpr($symbol, $type);
    }
  }

  private static function str_expr(Context $ctx, AST\StrExpr $expr): IR\StrExpr {
    return new IR\StrExpr($expr->value);
  }

  private static function num_expr(Context $ctx, AST\NumExpr $expr): IR\NumExpr {
    return new IR\NumExpr($expr->value);
  }

  // int_expr
}
