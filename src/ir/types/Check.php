<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir;
use Cthulhu\ir\names;
use Cthulhu\ir\nodes;

class Check {
  private $spans;
  private $names;
  private $types;

  private function __construct(ir\Table $spans, ir\Table $names) {
    $this->spans = $spans;
    $this->names = $names;
    $this->types = new ir\Table(); // Symbols -> type
    $this->exprs = new ir\Table(); // Expression nodes -> type
  }

  private function get_symbol_for_name(nodes\Name $name): names\Symbol {
    if ($symbol = $this->names->get($name)) {
      return $symbol;
    }
    throw new \Exception('no known symbol for name');
  }

  private function get_type_for_symbol(names\Symbol $symbol): Type {
    if ($type = $this->types->get($symbol)) {
      return $type;
    }
    throw new \Exception('symbol has not been type-checked yet');
  }

  private function set_type_for_symbol(names\Symbol $symbol, Type $type): void {
    $this->types->set($symbol, $type);
  }

  private function get_type_for_expr(nodes\Expr $expr): Type {
    if ($type = $this->exprs->get($expr)) {
      return $type;
    }

    $span = $this->spans->get($expr);
    $line = $span->from->line;
    $file = $span->from->file->filepath;
    throw new \Exception("expression has not been type-checked yet on line $line in $file");
  }

  private function set_type_for_expr(nodes\Expr $expr, Type $type): void {
    $this->exprs->set($expr, $type);
  }

  public static function types(
    ir\Table $spans,
    ir\Table $name_to_symbol,
    ir\Table $symbol_to_name,
    nodes\Program $prog
  ): array {
    $ctx = new self($spans, $name_to_symbol, $symbol_to_name);

    ir\Visitor::walk($prog, [
      'enter(FuncItem)' => function (nodes\FuncItem $item) use ($ctx) {
        self::enter_func_item($ctx, $item);
      },
      'exit(FuncItem)' => function (nodes\FuncItem $item) use ($ctx) {
        self::exit_func_item($ctx, $item);
      },
      'exit(NativeFuncItem)' => function (nodes\NativeFuncItem $item) use ($ctx) {
        self::exit_native_func_item($ctx, $item);
      },
      'exit(NativeTypeItem)' => function (nodes\NativeTypeItem $item) use ($ctx) {
        self::exit_native_type_item($ctx, $item);
      },
      'exit(LetStmt)' => function (nodes\LetStmt $stmt) use ($ctx) {
        self::exit_let_stmt($ctx, $stmt);
      },
      'exit(IfExpr)' => function (nodes\IfExpr $expr) use ($ctx) {
        self::exit_if_expr($ctx, $expr);
      },
      'exit(CallExpr)' => function (nodes\CallExpr $expr) use ($ctx) {
        self::exit_call_expr($ctx, $expr);
      },
      'exit(BinaryExpr)' => function (nodes\BinaryExpr $expr) use ($ctx) {
        self::exit_binary_expr($ctx, $expr);
      },
      'exit(UnaryExpr)' => function (nodes\UnaryExpr $expr) use ($ctx) {
        self::exit_unary_expr($ctx, $expr);
      },
      'RefExpr' => function (nodes\RefExpr $expr) use ($ctx) {
        self::ref_expr($ctx, $expr);
      },
      'StrExpr' => function (nodes\StrExpr $expr) use ($ctx) {
        self::str_expr($ctx, $expr);
      },
      'IntExpr' => function (nodes\IntExpr $expr) use ($ctx) {
        self::int_expr($ctx, $expr);
      },
      'BoolExpr' => function (nodes\BoolExpr $expr) use ($ctx) {
        self::bool_expr($ctx, $expr);
      },
      'exit(Block)' => function (nodes\Block $block) use ($ctx) {
        self::exit_block($ctx, $block);
      },
    ]);

    return [
      $ctx->types, // symbol -> type
      $ctx->exprs, // expression node -> type
    ];
  }

  /**
   * A sanity check to make sure that all Expr nodes in the IR have been
   * assigned a Type. Will throw an error if one of the nodes doesn't have a
   * type. Because this check requires an additional walk over the whole IR tree
   * and because this validation is checking for bugs in the type-checker, it
   * should only be run during compiler development.
   */
  public static function validate(ir\Table $exprs, nodes\Program $prog): void {
    ir\Visitor::walk($prog, [
      'Expr' => function (nodes\Expr $expr) use ($exprs) {
        if ($exprs->has($expr) === false) {
          throw new \Exception('missing type binding for an expression');
        }
      },
    ]);
  }

  private static function enter_func_item(self $ctx, nodes\FuncItem $item): void {
    $polys = [];
    foreach ($item->polys as $poly) {
      $poly_symbol = $ctx->get_symbol_for_name($poly);
      $polys[] = $poly_type = new GenericType($poly->value, $poly_symbol);
      $ctx->set_type_for_symbol($poly_symbol, $poly_type);
    }
    $inputs = [];
    foreach ($item->params as $param) {
      $inputs[] = $type = self::note_to_type($ctx, $param->note);
      $symbol = $ctx->get_symbol_for_name($param->name);
      $ctx->set_type_for_symbol($symbol, $type);
    }
    $output = self::note_to_type($ctx, $item->output);
    $type = new FuncType($polys, $inputs, $output);
    $symbol = $ctx->get_symbol_for_name($item->name);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function exit_func_item(self $ctx, nodes\FuncItem $item): void {
    $symbol = $ctx->get_symbol_for_name($item->name);
    $expected_return_type = $ctx->get_type_for_symbol($symbol)->output;

    $block_type = $ctx->get_type_for_expr($item->body);
    if ($last_stmt = $item->body->last_stmt()) {
      $span = $ctx->spans->get($last_stmt);
    } else {
      $span = $ctx->spans->get($item->body);
    }

    if ($expected_return_type->equals($block_type) === false) {
      throw Errors::wrong_return_type($span, $expected_return_type, $block_type);
    }
  }

  private static function exit_native_func_item(self $ctx, nodes\NativeFuncItem $item): void {
    $symbol = $ctx->get_symbol_for_name($item->name);
    $type = self::func_note_to_type($ctx, $item->note);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function exit_native_type_item(self $ctx, nodes\NativeTypeItem $item): void {
    switch ($item->name->value) {
      case 'Str':
        $type = new StrType();
        break;
      case 'Int':
        $type = new IntType();
        break;
      case 'Bool':
        $type = new BoolType();
        break;
      default:
        throw new \Exception('unknown native type');
    }

    $symbol = $ctx->names->get($item->name);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function exit_let_stmt(self $ctx, nodes\LetStmt $stmt): void {
    $symbol = $ctx->get_symbol_for_name($stmt->name);
    $type = $ctx->get_type_for_expr($stmt->expr);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function exit_if_expr(self $ctx, nodes\IfExpr $expr): void {
    $cond_type = $ctx->get_type_for_expr($expr->cond);
    if (BoolType::does_not_match($cond_type)) {
      $span = $ctx->spans->get($expr->cond);
      throw Errors::if_cond_not_bool($span, $cond_type);
    }

    $if_true_type = $ctx->get_type_for_expr($expr->if_true);
    $if_false_type = $expr->if_false
      ? $ctx->get_type_for_expr($expr->if_false)
      : new UnitType();

    if ($if_true_type->equals($if_false_type) === false) {
      $if_span = $ctx->spans->get($expr->if_true);
      if ($expr->if_false) {
        $else_span = $ctx->spans->get($expr->if_false);
        throw Errors::if_else_branch_disagreement(
          $if_span,
          $if_true_type,
          $else_span,
          $if_false_type
        );
      } else {
        throw Errors::if_branch_not_returning_unit($if_span, $if_true_type);
      }
    }

    $ctx->set_type_for_expr($expr, $if_true_type);
  }

  private static function exit_call_expr(self $ctx, nodes\CallExpr $expr): void {
    $generic_callee_type = $ctx->get_type_for_expr($expr->callee);
    if (FuncType::does_not_match($generic_callee_type)) {
      $span = $ctx->spans->get($expr->callee);
      throw Errors::call_to_non_function($span, $generic_callee_type);
    }

    $total_expected_notes = count($generic_callee_type->polys);
    $total_found_notes = count($expr->concretes);
    if ($total_found_notes !== $total_expected_notes) {
      $span = $ctx->spans->get($expr);
      throw Errors::call_with_wrong_poly_num($span, $total_expected_notes, $total_found_notes);
    }

    $concrete_types = [];
    foreach ($expr->concretes as $concrete) {
      $concrete_types[] = self::note_to_type($ctx, $concrete);
    }

    $replacements = [];
    foreach ($generic_callee_type->polys as $index => $poly) {
      $replacements[$poly->symbol->get_id()] = $concrete_types[$index];
    }
    $concrete_callee_type = $generic_callee_type->replace_generics($replacements);

    $total_expected_args = count($concrete_callee_type->inputs);
    $total_found_args = count($expr->args);
    if ($total_expected_args !== $total_found_args) {
      $span = $ctx->spans->get($expr);
      throw Errors::call_with_wrong_arg_num($span, $total_expected_args, $total_found_args);
    }

    foreach ($expr->args as $index => $arg) {
      $expected_type = $concrete_callee_type->inputs[$index];
      $arg_type = $ctx->get_type_for_expr($arg);
      if ($expected_type->accepts($arg_type) === false) {
        $span = $ctx->spans->get($arg);
        throw Errors::call_with_wrong_arg_type($span, $index, $expected_type, $arg_type);
      }
    }

    $ctx->set_type_for_expr($expr, $concrete_callee_type->output);
  }

  private static function exit_binary_expr(self $ctx, nodes\BinaryExpr $expr): void {
    $lhs = $ctx->get_type_for_expr($expr->left);
    $rhs = $ctx->get_type_for_expr($expr->right);
    $op = $expr->op;
    if ($type = $lhs->apply($op, $rhs)) {
      $ctx->set_type_for_expr($expr, $type);
    } else {
      $span = $ctx->spans->get($expr);
      throw Errors::unsupported_binary_operator($span, $op, $lhs, $rhs);
    }
  }

  private static function exit_unary_expr(self $ctx, nodes\UnaryExpr $expr): void {
    $rhs = $ctx->get_type_for_expr($expr->right);
    $op = $expr->op;
    if ($type = $rhs->apply($op)) {
      $ctx->set_type_for_expr($expr, $type);
    } else {
      $span = $ctx->spans->get($expr);
      throw Errors::unsupported_unary_operator($span, $op, $rhs);
    }
  }

  private static function ref_expr(self $ctx, nodes\RefExpr $expr): void {
    $symbol = $ctx->get_symbol_for_name($expr->ref->tail_segment);
    $type = $ctx->get_type_for_symbol($symbol);
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function str_expr(self $ctx, nodes\StrExpr $expr): void {
    $type = new StrType();
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function int_expr(self $ctx, nodes\IntExpr $expr): void {
    $type = new IntType();
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function bool_expr(self $ctx, nodes\BoolExpr $expr): void {
    $type = new BoolType();
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function exit_block(self $ctx, nodes\Block $block): void {
    $last_stmt = $block->last_stmt();
    if ($last_stmt instanceof nodes\ReturnStmt) {
      $type = $ctx->get_type_for_expr($last_stmt->expr);
      $ctx->set_type_for_expr($block, $type);
    } else {
      $ctx->set_type_for_expr($block, new UnitType());
    }
  }

  private static function note_to_type(self $ctx, nodes\Note $note): Type {
    switch (true) {
      case $note instanceof nodes\FuncNote:
        return self::func_note_to_type($ctx, $note);
      case $note instanceof nodes\NameNote:
        return self::name_note_to_type($ctx, $note);
      case $note instanceof nodes\UnitNote:
        return self::unit_note_to_type($ctx, $note);
      default:
        throw new \Exception('cannot type-check unknown note node: ' . get_class($note));
    }
  }

  private static function func_note_to_type(self $ctx, nodes\FuncNote $note): Type {
    $inputs = [];
    foreach ($note->inputs as $input) {
      $inputs[] = self::note_to_type($ctx, $input);
    }
    $output = self::note_to_type($ctx, $note->output);
    return new FuncType([], $inputs, $output);
  }

  private static function name_note_to_type(self $ctx, nodes\NameNote $note): Type {
    $symbol = $ctx->get_symbol_for_name($note->ref->tail_segment);
    return $ctx->get_type_for_symbol($symbol);
  }

  private static function unit_note_to_type(): Type {
    return new UnitType();
  }
}