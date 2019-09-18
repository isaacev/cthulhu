<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\IR;
use Cthulhu\Types;

class Analyzer {
  public static function ast_to_module(AST\File $file): IR\SourceModule {
    $ctx = new Context($file->file);
    $items = [];
    foreach ($file->items as $item) {
      $items[] = self::item($ctx, $item);
    }
    return new IR\SourceModule($file->file, $ctx->used_builtins, $ctx->pop_module_scope(), $items);
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
    $fn_name = $item->name->ident;
    $origin = $item->span->extended_to($item->returns->span);
    $symbol = new IR\Symbol($fn_name, $origin, $ctx->current_module_scope()->symbol);

    // Determine function type signature
    $param_types = [];
    foreach ($item->params as $param) {
      $param_types[] = self::annotation_to_type($ctx, $param->note);
    }
    $return_origin = $item->returns->span;
    $return_type = self::annotation_to_type($ctx, $item->returns);
    $type = new Types\FnType($param_types, $return_type);
    $ctx->current_module_scope()->add($symbol, $type);

    // Build new block scope and add parameters to the scope
    $ctx->push_block_scope();
    $param_symbols = [];
    foreach ($item->params as $index => $param) {
      $param_name = $param->name->ident;
      $param_origin = $param->span;
      $param_symbol = new IR\Symbol($param_name, $param_origin, null);
      $param_symbols[] = $param_symbol;
      $param_type = $param_types[$index];
      $ctx->current_block_scope()->add($param_symbol, $param_type);
    }

    // Verify that the function body returns the correct type
    $ctx->push_expected_return($item, $return_type);
    $body = self::block($ctx, $item->body);
    $found_type = $body->type();
    $ctx->pop_expected_return();

    if ($return_type->accepts($found_type) === false) {
      // This condition is necessary because even though the `$return_type` was
      // pushed onto the expected return stack, that stack is only read when an
      // `AST\SemiStmt` (implicit return) is encountered. If the block is has
      // a branch that returns *nothing* and the return type is not `Void`, this
      // check will catch and report those errors.
      $block_span = $item->body->span;
      $wanted_span = $item->returns->span;
      $wanted_type = $return_type;
      $last_stmt = $body->last_stmt();
      $last_ast_stmt = end($item->body->stmts);
      $last_semi = $last_ast_stmt instanceof AST\SemiStmt ? $last_ast_stmt->semi->span : null;
      throw Errors::function_returns_nothing($ctx->file, $block_span, $wanted_span, $wanted_type, $last_stmt, $last_semi);
    }

    return new IR\FnItem($symbol, $param_symbols, $type, $ctx->pop_block_scope(), $body);
  }

  private static function block(Context $ctx, AST\BlockNode $block): IR\BlockNode {
    $ctx->push_block_scope();
    $stmts = [];
    $total_stmts = count($block->stmts);
    foreach ($block->stmts as $stmt) {
      $stmts[] = self::stmt($ctx, $stmt);
    }
    return new IR\BlockNode($ctx->pop_block_scope(), $stmts);
  }

  private static function stmt(Context $ctx, AST\Stmt $stmt): IR\Stmt {
    switch (true) {
      case $stmt instanceof AST\LetStmt:
        return self::let_stmt($ctx, $stmt);
      case $stmt instanceof AST\SemiStmt:
        return self::semi_stmt($ctx, $stmt);
      case $stmt instanceof AST\ExprStmt:
        return self::expr_stmt($ctx, $stmt);
      default:
        throw new \Exception('illegal statement in block');
    }
  }

  private static function let_stmt(Context $ctx, AST\LetStmt $stmt): IR\Stmt {
    $name = $stmt->name->ident;
    $origin = $stmt->name->span;
    $symbol = new IR\Symbol($name, $origin, null);
    $expr = self::expr($ctx, $stmt->expr);
    $ctx->current_block_scope()->add($symbol, $expr->type());
    return new IR\AssignStmt($symbol, $expr);
  }

  private static function semi_stmt(Context $ctx, AST\SemiStmt $stmt): IR\Stmt {
    $expr = self::expr($ctx, $stmt->expr);
    return new IR\SemiStmt($expr);
  }

  private static function expr_stmt(Context $ctx, AST\ExprStmt $stmt): IR\Stmt {
    $expr = self::expr($ctx, $stmt->expr);

    // list($fn_node, $return_type) = $ctx->current_expected_return();
    // $found_type = $expr->type();
    // if ($return_type->accepts($found_type) === false) {
    //   $found_span = $stmt->span;
    //   $wanted_span = $fn_node->returns->span;
    //   throw Errors::incorrect_return_type($ctx->file, $found_span, $found_type, $wanted_span, $return_type);
    // }

    return new IR\ReturnStmt($expr);
  }

  private static function expr(Context $ctx, AST\Expr $expr): IR\Expr {
    switch (true) {
      case $expr instanceof AST\IfExpr:
        return self::if_expr($ctx, $expr);
      case $expr instanceof AST\CallExpr:
        return self::call_expr($ctx, $expr);
      case $expr instanceof AST\PathExpr:
        return self::path_expr($ctx, $expr);
      case $expr instanceof AST\StrExpr:
        return self::str_expr($ctx, $expr);
      case $expr instanceof AST\NumExpr:
        return self::num_expr($ctx, $expr);
      case $expr instanceof AST\BoolExpr:
        return self::bool_expr($ctx, $expr);
    }
  }

  // fn_expr

  /**
   * If-expression analysis
   *
   * - If the true-branch of an if-expression returns a value, all other
   *   branches must also return a value with a compatible type.
   * - If the false-branch is not given, the else-branch is treated as if it
   *   does not return a type.
   */
  private static function if_expr(Context $ctx, AST\IfExpr $expr): IR\Expr {
    $cond = self::expr($ctx, $expr->condition);
    if (($cond->type() instanceof Types\BoolType) === false) {
      $found_span = $expr->condition->span;
      $found_type = $cond->type();
      throw Errors::condition_not_bool($ctx->file, $found_span, $found_type);
    }

    $if_true = self::block($ctx, $expr->if_clause);
    $if_true_type = $if_true->type();

    if ($expr->else_clause !== null) {
      $if_false = self::block($ctx, $expr->else_clause);
      $if_false_type = $if_false->type();
      if ($if_true_type->accepts($if_false_type) === false) {
        $if_true_span = $expr->if_clause->span;

        // When determining which span to use for both blocks, if the block
        // implicitly returns its last statement, use the span from that
        // statement. If the block is empty or doesn't have an implicit return,
        // use the span of the entire block.
        $if_true_span = $expr->if_clause->returns()
          ? $expr->if_clause->last_stmt()->span
          : $expr->if_clause->span;

        $if_false_span = $expr->else_clause->returns()
          ? $expr->else_clause->last_stmt()->span
          : $expr->else_clause->span;

        throw Errors::incompatible_if_and_else_types(
          $ctx->file,
          $if_true_span,
          $if_true_type,
          $if_false_span,
          $if_false_type);
      }
    } else {
      // The if-expression doesn't have a false-block, this means the block
      // implicitly returns the Void type. If the true-block returns a non-Void
      // type, throw an error because of type incompatibility.
      if (($if_true_type instanceof Types\VoidType) === false) {
        // When determining which span to use for both blocks, if the block
        // implicitly returns its last statement, use the span from that
        // statement. If the block is empty or doesn't have an implicit return,
        // use the span of the entire block.
        $if_true_span = $expr->if_clause->returns()
          ? $expr->if_clause->last_stmt()->span
          : $expr->if_clause->span;

        $if_true_block_span = $expr->if_clause->span;

        throw Errors::if_block_incompatible_with_void(
          $ctx->file,
          $if_true_span,
          $if_true_type,
          $if_true_block_span);
      }
    }

    return new IR\IfExpr($if_true_type, $cond, $if_true, $if_false);
  }

  private static function call_expr(Context $ctx, AST\CallExpr $expr): IR\CallExpr {
    $callee = self::expr($ctx, $expr->callee);
    if (($callee->type() instanceof Types\FnType) === false) {
      throw new Types\Errors\TypeMismatch('function', $callee->type());
    }
    $args = [];
    $wanted_num_args = count($callee->type()->params);
    $given_num_args = count($expr->args);
    if ($wanted_num_args !== $given_num_args) {
      throw Errors::func_called_with_wrong_num_or_args(
        $ctx->file,
        $expr->span,
        $given_num_args,
        $callee->type()
      );
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
      if ($symbol === null) {
        throw Errors::unknown_local_variable($ctx->file, $expr->span, $name);
      }
      $type = $ctx->current_block_scope()->lookup($symbol);
      return new IR\ReferenceExpr($symbol, $type);
    }

    // Treat path as a reference to a name within a module
    $module_segments = array_slice($expr->segments, 0, -1);
    $value_segment = end($expr->segments);

    // Get the reference to the last module in the path
    $current_module = $ctx->current_module_scope();
    foreach ($module_segments as $index => $module_segment) {
      $module_symbol = $current_module->to_symbol($module_segment->ident);
      if ($module_symbol === null) {
        throw Errors::unknown_submodule(
          $ctx->file,
          $current_module->symbol,
          $module_segment->span,
          $module_segment->ident
        );
      }

      $lookup_result = $current_module->lookup($module_symbol);
      if ($lookup_result instanceof IR\ModuleScope) {
        $current_module = $lookup_result;
        continue;
      }

      $next_segment = $expr->segments[$index + 1];
      throw Errors::value_referenced_as_module(
        $ctx->file,
        $expr->span->extended_to($module_segment->span),
        $module_symbol,
        $lookup_result,
        $expr->span->extended_to($next_segment->span)
      );
    }

    $value_symbol = $current_module->to_symbol($value_segment->ident);
    if ($value_symbol === null) {
      throw Errors::unknown_module_field(
        $ctx->file,
        $current_module->symbol,
        $value_segment->span,
        $value_segment->ident
      );
    }
    $type = $current_module->lookup($value_symbol);
    return new IR\ReferenceExpr($value_symbol, $type);
  }

  private static function str_expr(Context $ctx, AST\StrExpr $expr): IR\StrExpr {
    return new IR\StrExpr($expr->value);
  }

  private static function num_expr(Context $ctx, AST\NumExpr $expr): IR\NumExpr {
    return new IR\NumExpr($expr->value);
  }

  private static function bool_expr(Context $ctx, AST\BoolExpr $expr): IR\BoolExpr {
    return new IR\BoolExpr($expr->value);
  }

  // int_expr

  /**
   * Utility methods
   */
  private static function annotation_to_type(Context $ctx, AST\Annotation $note): Types\Type {
    switch (true) {
      case $note instanceof AST\NamedAnnotation:
        return self::named_annotation_to_type($ctx, $note);
      default:
        throw new \Exception('unknown annotation type');
    }
  }

  private static function named_annotation_to_type(Context $ctx, AST\NamedANnotation $note): Types\Type {
    switch ($note->name) {
      case 'Str':
        return new Types\StrType();
      case 'Num':
        return new Types\NumType();
      case 'Bool':
        return new Types\BoolType();
      case 'Void':
        return new Types\VoidType();
      default:
        throw Errors::unknown_named_type($note);
    }
  }
}
