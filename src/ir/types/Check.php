<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir;
use Cthulhu\ir\names;
use Cthulhu\ir\nodes;

class Check {
  private $spans;
  private $name_to_symbol;
  private $symbol_to_name;
  private $types;
  private $exprs;

  private function __construct(ir\Table $spans, ir\Table $name_to_symbol, ir\Table $symbol_to_name) {
    $this->spans = $spans;
    $this->name_to_symbol = $name_to_symbol;
    $this->symbol_to_name = $symbol_to_name;
    $this->types = new ir\Table(); // Symbols -> type
    $this->exprs = new ir\Table(); // Expression nodes -> type
  }

  private function get_symbol_for_name(nodes\Name $name): names\Symbol {
    if ($symbol = $this->name_to_symbol->get($name)) {
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
      'enter(FuncHead)' => function (nodes\FuncHead $head) use ($ctx) {
        self::enter_func_head($ctx, $head);
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
      'exit(ListExpr)' => function (nodes\ListExpr $expr) use ($ctx) {
        self::exit_list_expr($ctx, $expr);
      },
      'RefExpr' => function (nodes\RefExpr $expr) use ($ctx) {
        self::ref_expr($ctx, $expr);
      },
      'StrLiteral' => function (nodes\StrLiteral $expr) use ($ctx) {
        self::str_literal($ctx, $expr);
      },
      'IntLiteral' => function (nodes\IntLiteral $expr) use ($ctx) {
        self::int_literal($ctx, $expr);
      },
      'BoolLiteral' => function (nodes\BoolLiteral $expr) use ($ctx) {
        self::bool_literal($ctx, $expr);
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

  private static function enter_func_head(self $ctx, nodes\FuncHead $head): void {
    $type_param_names = [];
    foreach ($head->params as $param) {
      ir\Visitor::walk($param->note, [
        'ParamNote' => function (nodes\ParamNote $note) use (&$type_param_names) {
          $type_param_names[] = $note->name;
        },
      ]);
    }

    $inputs = [];
    foreach ($head->params as $param) {
      $inputs[] = $type = self::note_to_type($ctx, $param->note);
      $symbol = $ctx->get_symbol_for_name($param->name);
      $ctx->set_type_for_symbol($symbol, $type);
    }
    $output = self::note_to_type($ctx, $head->output);
    $type = new FuncType($inputs, $output);
    $symbol = $ctx->get_symbol_for_name($head->name);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function exit_func_item(self $ctx, nodes\FuncItem $item): void {
    $symbol = $ctx->get_symbol_for_name($item->head->name);
    $expected_return_type = $ctx->get_type_for_symbol($symbol)->output;

    $block_type = $ctx->get_type_for_expr($item->body);
    if ($last_stmt = $item->body->last_stmt()) {
      $span = $ctx->spans->get($last_stmt);
    } else {
      $span = $ctx->spans->get($item->body);
    }

    $is_generic = $expected_return_type instanceof GenericType;
    $equals_generic = $is_generic ? $expected_return_type->equals($block_type) : false;
    $ret_accepts = $expected_return_type->accepts_as_return($block_type);
    if (($is_generic ? $equals_generic : $ret_accepts) === false) {
      throw Errors::wrong_return_type($span, $expected_return_type, $block_type);
    }
  }

  private static function exit_native_func_item(self $ctx, nodes\NativeFuncItem $item): void {
    $type_param_names = [];
    foreach ($item->note->inputs as $note) {
      ir\Visitor::walk($note, [
        'ParamNote' => function (nodes\ParamNote $note) use (&$type_param_names) {
          $type_param_names[] = $note->name;
        },
      ]);
    }

    $inputs = [];
    foreach ($item->note->inputs as $input) {
      $inputs[] = self::note_to_type($ctx, $input);
    }
    $output = self::note_to_type($ctx, $item->note->output);

    $symbol = $ctx->get_symbol_for_name($item->name);
    $type = new FuncType($inputs, $output);
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

    $symbol = $ctx->name_to_symbol->get($item->name);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function exit_let_stmt(self $ctx, nodes\LetStmt $stmt): void {
    $symbol = $ctx->get_symbol_for_name($stmt->name);
    $expr_type = $ctx->get_type_for_expr($stmt->expr);

    if ($stmt->note !== null) {
      $note_type = self::note_to_type($ctx, $stmt->note);
      if ($note_type->accepts_as_parameter($expr_type) === false) {
        throw Errors::let_note_does_not_match_expr(
          $ctx->spans->get($stmt->note),
          $note_type,
          $ctx->spans->get($stmt->expr),
          $expr_type
        );
      }
      $type = $note_type;
    } else {
      $type = $expr_type;
    }

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

    if ($unified_type = $if_true_type->unify($if_false_type)) {
      $ctx->set_type_for_expr($expr, $unified_type);
    } else {
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
  }

  private static function exit_call_expr(self $ctx, nodes\CallExpr $expr): void {
    /**
     * 1.
     * Determine the type of the callee expression and make sure that it is a FuncType.
     */
    $parameterized_callee_type = $ctx->get_type_for_expr($expr->callee);
    if (FuncType::does_not_match($parameterized_callee_type)) {
      $span = $ctx->spans->get($expr->callee);
      throw Errors::call_to_non_function($span, $parameterized_callee_type);
    }
    assert($parameterized_callee_type instanceof FuncType);

    /**
     * 2.
     * Make sure that the correct number of arguments were passed to the callee.
     */
    $num_params = count($parameterized_callee_type->inputs);
    $num_args = count($expr->args);
    if ($num_params !== $num_args) {
      $span = $ctx->spans->get($expr);
      throw Errors::call_with_wrong_arg_num($span, $num_params, $num_args);
    }

    $arg_types = [];
    $param_components = []; // ParamSymbol -> Type[]
    foreach ($expr->args as $index => $arg) {
      $arg_type = $arg_types[] = $ctx->exprs->get($arg);
      $param_type = $parameterized_callee_type->inputs[$index];

      /**
       * 3.
       * Make sure that the argument expressions have the correct types.
       */
      if ($param_type->accepts_as_parameter($arg_type)) {
        /**
         * 4.
         * Using the argument types, find types for each callee type parameter that
         * don't violate any of the callee signature's type relations.
         */
        self::find_type_param_components($ctx, $param_components, $param_type, $arg_type);
      } else {
        $span = $ctx->spans->get($arg);
        throw Errors::call_with_wrong_arg_type($span, $index, $param_type, $arg_type);
      }
    }

    $type_param_solutions = [];
    foreach ($param_components as $symbol_id => $components) {
      assert(!empty($components));
      $unification = $components[0];
      for ($i = 1; $i < count($components); $i++) {
        $component = $components[$i];
        if ($attempt = $unification->unify($component)) {
          $unification = $attempt;
        } else {
          $span = $ctx->spans->get($expr);
          $name = $ctx->symbol_to_name->get_by_id($symbol_id);
          throw Errors::unsolvable_type_parameter($span, $name, $unification, $component);
        }
      }
      $type_param_solutions[$symbol_id] = $unification;
    }

    $concrete_callee_type = self::bind_type_params($ctx, $parameterized_callee_type, $type_param_solutions);
    $ctx->set_type_for_expr($expr, $concrete_callee_type->output);
  }

  private static function find_type_param_components(self $ctx, array &$components, Type $param, Type $arg): void {
    switch (true) {
      case $param instanceof ParamType: {
        $param_symbol = $ctx->get_symbol_for_name($param->name);
        if (isset($components[$param_symbol->get_id()])) {
          $components[$param_symbol->get_id()][] = $arg;
        } else {
          $components[$param_symbol->get_id()] = [ $arg ];
        }
        break;
      }
      case $param instanceof FuncType: {
        assert($arg instanceof FuncType);
        foreach ($param->inputs as $index => $param_input) {
          self::find_type_param_components($ctx, $components, $param_input, $arg->inputs[$index]);
        }
        self::find_type_param_components($ctx, $components, $param->output, $arg->output);
        break;
      }
      case $param instanceof ListType: {
        assert($arg instanceof ListType);
        if (isset($param->element)) {
          self::find_type_param_components($ctx, $components, $param->element, $arg->element);
        }
        break;
      }
      case $param instanceof TupleType: {
        assert($arg instanceof TupleType);
        foreach ($param->members as $index => $param_member) {
          self::find_type_param_components($ctx, $components, $param_member, $arg->members[$index]);
        }
        break;
      }
    }
  }

  private static function bind_type_params(self $ctx, Type $parameterized, array $solutions): Type {
    switch (true) {
      case $parameterized instanceof ParamType:
        $symbol_id = $ctx->name_to_symbol->get($parameterized->name)->get_id();
        return $solutions[$symbol_id];
      case $parameterized instanceof FuncType:
        $inputs = [];
        foreach ($parameterized->inputs as $input) {
          $inputs[] = self::bind_type_params($ctx, $input, $solutions);
        }
        $output = self::bind_type_params($ctx, $parameterized->output, $solutions);
        return new FuncType($inputs, $output);
      case $parameterized instanceof ListType:
        if (isset($parameterized->element)) {
          $element = self::bind_type_params($ctx, $parameterized->element, $solutions);
          return new ListType($element);
        } else {
          return new ListType();
        }
      case $parameterized instanceof TupleType:
        $members = [];
        foreach ($parameterized->members as $member) {
          $members[] = self::bind_type_params($ctx, $member, $solutions);
        }
        return new TupleType($members);
      default:
        return $parameterized;
    }
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

  private static function exit_list_expr(self $ctx, nodes\ListExpr $expr): void {
    $unified_type = null;
    foreach ($expr->elements as $index => $element_expr) {
      $element_type = $ctx->get_type_for_expr($element_expr);
      if ($unified_type === null) {
        $unified_type = $element_type;
        continue;
      }

      if ($candidate_type = $unified_type->unify($element_type)) {
        $unified_type = $candidate_type;
        continue;
      }

      $span = $ctx->spans->get($element_expr);
      throw Errors::mismatched_list_element_types(
        $span,
        $unified_type,
        $index + 1,
        $element_type
      );
    }

    $type = new ListType($unified_type);
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function ref_expr(self $ctx, nodes\RefExpr $expr): void {
    $symbol = $ctx->get_symbol_for_name($expr->ref->tail_segment);
    $type = $ctx->get_type_for_symbol($symbol);
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function str_literal(self $ctx, nodes\StrLiteral $expr): void {
    $type = new StrType();
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function int_literal(self $ctx, nodes\IntLiteral $expr): void {
    $type = new IntType();
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function bool_literal(self $ctx, nodes\BoolLiteral $expr): void {
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
      case $note instanceof nodes\ListNote:
        return self::list_note_to_type($ctx, $note);
      case $note instanceof nodes\ParamNote:
        return self::param_note_to_type($ctx, $note);
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

  private static function list_note_to_type(self $ctx, nodes\ListNote $note): Type {
    $elements = self::note_to_type($ctx, $note->elements);
    return new ListType($elements);
  }

  private static function param_note_to_type(self $ctx, nodes\ParamNote $note): Type {
    return new ParamType($note->name);
  }
}
