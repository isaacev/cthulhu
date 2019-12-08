<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir;
use Cthulhu\ir\names;
use Cthulhu\ir\nodes;
use Exception;

class Check {
  private function get_symbol_for_name(nodes\Name $name): names\Symbol {
    if ($symbol = $name->get('symbol')) {
      return $symbol;
    }
    die('no known symbol for name');
  }

  private function get_type_for_symbol(names\Symbol $symbol): Type {
    if ($type = $symbol->get('type')) {
      return $type;
    }
    die('symbol has not been type-checked yet');
  }

  private function set_type_for_symbol(names\Symbol $symbol, Type $type): void {
    $symbol->set('type', $type);
  }

  private function get_type_for_expr(nodes\Expr $expr): Type {
    if ($type = $expr->get('type')) {
      return $type;
    }

    $span = $expr->get('span');
    $line = $span->from->line;
    $file = $span->from->file->filepath;
    die("expression has not been type-checked yet on line $line in $file");
  }

  private function set_type_for_expr(nodes\Expr $expr, Type $type): void {
    $expr->set('type', $type);
  }

  public static function types(nodes\Program $prog): void {
    $ctx = new self();

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
      'exit(UnionItem)' => function (nodes\UnionItem $item) use ($ctx) {
        self::exit_union_item($ctx, $item);
      },
      'exit(LetStmt)' => function (nodes\LetStmt $stmt) use ($ctx) {
        self::exit_let_stmt($ctx, $stmt);
      },
      'enter(MatchArm)' => function (nodes\MatchArm $arm, ir\Path $path) use ($ctx) {
        self::enter_match_arm($ctx, $arm, $path);
      },
      'exit(MatchExpr)' => function (nodes\MatchExpr $expr) use ($ctx) {
        self::exit_match_expr($ctx, $expr);
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
      'exit(VariantConstructorExpr)' => function (nodes\VariantConstructorExpr $expr) use ($ctx) {
        self::exit_variant_constructor_expr($ctx, $expr);
      },
      'RefExpr' => function (nodes\RefExpr $expr) use ($ctx) {
        self::ref_expr($ctx, $expr);
      },
      'StrLiteral' => function (nodes\StrLiteral $expr) use ($ctx) {
        self::str_literal($ctx, $expr);
      },
      'FloatLiteral' => function (nodes\FloatLiteral $expr) use ($ctx) {
        self::float_literal($ctx, $expr);
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
  }

  /**
   * A sanity check to make sure that all Expr nodes in the IR have been
   * assigned a Type. Will throw an error if one of the nodes doesn't have a
   * type. Because this check requires an additional walk over the whole IR tree
   * and because this validation is checking for bugs in the type-checker, it
   * should only be run during compiler development.
   *
   * @param nodes\Program $prog
   */
  public static function validate(nodes\Program $prog): void {
    ir\Visitor::walk($prog, [
      'Expr' => function (nodes\Expr $expr) {
        if ($expr->has('type') === false) {
          throw new Exception('missing type binding for an expression');
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
      $symbol   = $ctx->get_symbol_for_name($param->name);
      $ctx->set_type_for_symbol($symbol, $type);
    }
    $output = self::note_to_type($ctx, $head->output);
    $type   = new FuncType($inputs, $output);
    $symbol = $ctx->get_symbol_for_name($head->name);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function exit_func_item(self $ctx, nodes\FuncItem $item): void {
    $symbol               = $ctx->get_symbol_for_name($item->head->name);
    $expected_return_type = $ctx->get_type_for_symbol($symbol)->output;

    $block_type = $ctx->get_type_for_expr($item->body);
    if ($last_stmt = $item->body->last_stmt()) {
      $span = $last_stmt->get('span');
    } else {
      $span = $item->body->get('span');
    }

    if ($expected_return_type->accepts_as_return($block_type) === false) {
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
    $type   = new FuncType($inputs, $output);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function exit_native_type_item(self $ctx, nodes\NativeTypeItem $item): void {
    switch ($item->name->value) {
      case 'Str':
        $type = new StrType();
        break;
      case 'Float':
        $type = new FloatType();
        break;
      case 'Int':
        $type = new IntType();
        break;
      case 'Bool':
        $type = new BoolType();
        break;
      default:
        throw new Exception('unknown native type');
    }

    $ctx->set_type_for_symbol($item->name->get('symbol'), $type);
  }

  private static function exit_union_item(self $ctx, nodes\UnionItem $item): void {
    $params = [];
    foreach ($item->params as $param) {
      $params[] = self::note_to_type($ctx, $param);
    }

    $variants = [];
    foreach ($item->variants as $variant) {
      if ($variant instanceof nodes\NamedVariantDeclNode) {
        $mapping = [];
        foreach ($variant->fields as $field) {
          $mapping[$field->name->value] = self::note_to_type($ctx, $field->note);
        }
        $variants[$variant->name->value] = new NamedVariantFields($mapping);
      } else if ($variant instanceof nodes\OrderedVariantDeclNode) {
        $order = [];
        foreach ($variant->members as $member) {
          $order[] = self::note_to_type($ctx, $member);
        }
        $variants[$variant->name->value] = new OrderedVariantFields($order);
      } else {
        $variants[$variant->name->value] = new UnitVariantFields();
      }
    }
    $symbol = $item->name->get('symbol');
    assert($symbol instanceof names\RefSymbol);
    $ref  = self::build_ref_from_symbol($ctx, $symbol);
    $type = new UnionType($symbol, $ref, $params, $variants);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  static function build_ref_from_symbol(self $ctx, names\RefSymbol $tail_symbol): nodes\Ref {
    $tail_segment = $tail_symbol->get('node');
    assert($tail_segment instanceof nodes\Name);

    $head_segments = [];
    $head_symbol   = $tail_symbol->parent;
    while ($head_symbol !== null) {
      $head_segment = $head_symbol->get('node');
      array_unshift($head_segments, $head_segment);
      $head_symbol = $head_symbol->parent;
    }

    assert(!empty($head_segments));
    return new nodes\Ref(true, $head_segments, $tail_segment);
  }

  private static function exit_let_stmt(self $ctx, nodes\LetStmt $stmt): void {
    $symbol    = $ctx->get_symbol_for_name($stmt->name);
    $expr_type = $ctx->get_type_for_expr($stmt->expr);

    if ($stmt->note !== null) {
      $note_type = self::note_to_type($ctx, $stmt->note);
      if ($note_type->accepts_as_parameter($expr_type) === false) {
        throw Errors::let_note_does_not_match_expr(
          $stmt->note->get('span'),
          $note_type,
          $stmt->expr->get('span'),
          $expr_type
        );
      }
      $type = $note_type;
    } else {
      $type = $expr_type;
    }

    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function enter_match_arm(self $ctx, nodes\MatchArm $arm, ir\Path $path): void {
    $match_expr = $path->parent->node;
    assert($match_expr instanceof nodes\MatchExpr);
    $disc_type = $ctx->get_type_for_expr($match_expr->disc->expr);
    $pattern   = $arm->pattern;
    self::check_pattern($ctx, $pattern, $disc_type);
  }

  private static function check_pattern(self $ctx, nodes\Pattern $pattern, Type $type): void {
    switch (true) {
      case $pattern instanceof nodes\VariantPattern:
        self::check_variant_pattern($ctx, $pattern, $type);
        break;
      case $pattern instanceof nodes\WildcardPattern:
        self::check_wildcard_pattern($ctx, $pattern, $type);
        break;
      case $pattern instanceof nodes\VariablePattern:
        self::check_variable_pattern($ctx, $pattern, $type);
        break;
      case $pattern instanceof nodes\StrConstPattern:
        self::check_str_pattern($ctx, $pattern, $type);
        break;
      case $pattern instanceof nodes\FloatConstPattern:
        self::check_float_pattern($ctx, $pattern, $type);
        break;
      case $pattern instanceof nodes\IntConstPattern:
        self::check_int_pattern($ctx, $pattern, $type);
        break;
      case $pattern instanceof nodes\BoolConstPattern:
        self::check_bool_pattern($ctx, $pattern, $type);
        break;
      default:
        die('unreachable');
    }
  }

  private static function check_variant_pattern(self $ctx, nodes\VariantPattern $pattern, Type $type): void {
    assert($type instanceof UnionType);

    $variant_symbol = $ctx->get_symbol_for_name($pattern->ref->tail_segment);
    $union_symbol   = $variant_symbol->parent;
    $union_type     = $ctx->get_type_for_symbol($union_symbol);

    assert($union_type instanceof UnionType);

    assert($union_type->accepts_as_parameter($type)); // ???

    $arguments = $type->get_variant_fields($pattern->ref->tail_segment->value);
    $fields    = $pattern->fields;
    if ($arguments instanceof UnitVariantFields) {
      assert($fields === null);
    } else if ($arguments instanceof NamedVariantFields) {
      assert($fields instanceof nodes\NamedVariantPatternFields);
      foreach ($fields->mapping as $field) {
        self::check_pattern($ctx, $field->pattern, $arguments->mapping[$field->name->value]);
      }
    } else if ($arguments instanceof OrderedVariantFields) {
      assert($fields instanceof nodes\OrderedVariantPatternFields);
      foreach ($fields->order as $index => $field) {
        self::check_pattern($ctx, $field->pattern, $arguments->order[$index]);
      }
    } else {
      die('unreachable');
    }
  }

  private static function check_wildcard_pattern(self $ctx, nodes\WildcardPattern $pattern, Type $type): void {
    // do nothing
  }

  private static function check_variable_pattern(self $ctx, nodes\VariablePattern $pattern, Type $type): void {
    $symbol = $ctx->get_symbol_for_name($pattern->name);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  private static function check_str_pattern(self $ctx, nodes\StrConstPattern $pattern, Type $type): void {
    if (StrType::matches($type) === false) {
      throw Errors::incompatible_pattern($pattern->get('span'), $type);
    }
  }

  private static function check_float_pattern(self $ctx, nodes\FloatConstPattern $pattern, Type $type): void {
    if (FloatType::matches($type) === false) {
      throw Errors::incompatible_pattern($pattern->get('span'), $type);
    }
  }

  private static function check_int_pattern(self $ctx, nodes\IntConstPattern $pattern, Type $type): void {
    if (IntType::matches($type) === false) {
      throw Errors::incompatible_pattern($pattern->get('span'), $type);
    }
  }

  private static function check_bool_pattern(self $ctx, nodes\BoolConstPattern $pattern, Type $type): void {
    if (BoolType::matches($type) === false) {
      throw Errors::incompatible_pattern($pattern->get('span'), $type);
    }
  }

  private static function exit_match_expr(self $ctx, nodes\MatchExpr $expr): void {
    assert(empty($expr->arms) === false);

    $match_type = $ctx->get_type_for_expr($expr->arms[0]->handler->stmt->expr);
    foreach (array_slice($expr->arms, 1) as $arm) {
      $arm_type = $ctx->get_type_for_expr($arm->handler->stmt->expr);
      if ($unified_type = $match_type->unify($arm_type)) {
        $match_type = $unified_type;
      } else {
        $arm_span = $arm->get('span');
        throw Errors::match_arm_disagreement(
          $arm_span,
          $arm_type,
          $match_type
        );
      }
    }

    $ctx->set_type_for_expr($expr, $match_type);
  }

  private static function exit_if_expr(self $ctx, nodes\IfExpr $expr): void {
    $cond_type = $ctx->get_type_for_expr($expr->cond);
    if (BoolType::does_not_match($cond_type)) {
      $span = $expr->cond->get('span');
      throw Errors::if_cond_not_bool($span, $cond_type);
    }

    $if_true_type  = $ctx->get_type_for_expr($expr->if_true);
    $if_false_type = $expr->if_false
      ? $ctx->get_type_for_expr($expr->if_false)
      : new UnitType();

    if ($unified_type = $if_true_type->unify($if_false_type)) {
      $ctx->set_type_for_expr($expr, $unified_type);
    } else {
      $if_span = $expr->if_true->get('span');
      if ($expr->if_false) {
        $else_span = $expr->if_false->get('span');
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
      $span = $expr->callee->get('span');
      throw Errors::call_to_non_function($span, $parameterized_callee_type);
    }
    assert($parameterized_callee_type instanceof FuncType);

    /**
     * 2.
     * Make sure that the correct number of arguments were passed to the callee.
     */
    $num_params = count($parameterized_callee_type->inputs);
    $num_args   = count($expr->args);
    if ($num_params !== $num_args) {
      $span = $expr->get('span');
      throw Errors::call_with_wrong_arg_num($span, $num_params, $num_args);
    }

    $arg_types        = [];
    $param_components = []; // ParamSymbol -> Type[]
    foreach ($expr->args as $index => $arg) {
      $arg_type   = $arg_types[] = $arg->get('type');
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
        $span = $arg->get('span');
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
          $span = $expr->get('span');
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
      case $param instanceof ParamType:
      {
        $param_symbol = $ctx->get_symbol_for_name($param->name);
        if (isset($components[$param_symbol->get_id()])) {
          $components[$param_symbol->get_id()][] = $arg;
        } else {
          $components[$param_symbol->get_id()] = [ $arg ];
        }
        break;
      }
      case $param instanceof FuncType:
      {
        assert($arg instanceof FuncType);
        foreach ($param->inputs as $index => $param_input) {
          self::find_type_param_components($ctx, $components, $param_input, $arg->inputs[$index]);
        }
        self::find_type_param_components($ctx, $components, $param->output, $arg->output);
        break;
      }
      case $param instanceof ListType:
      {
        assert($arg instanceof ListType);
        if (isset($param->element)) {
          self::find_type_param_components($ctx, $components, $param->element, $arg->element);
        }
        break;
      }
      case $param instanceof UnionType:
      {
        assert($arg instanceof UnionType);
        foreach ($param->params as $index => $union_param) {
          self::find_type_param_components($ctx, $components, $union_param, $arg->params[$index]);
        }
        break;
      }
    }
  }

  private static function bind_type_params(self $ctx, Type $parameterized, array $solutions): Type {
    return $parameterized->bind_parameters($solutions);
  }

  private static function exit_binary_expr(self $ctx, nodes\BinaryExpr $expr): void {
    $lhs = $ctx->get_type_for_expr($expr->left);
    $rhs = $ctx->get_type_for_expr($expr->right);
    $op  = $expr->op;
    if ($type = $lhs->apply($op, $rhs)) {
      $ctx->set_type_for_expr($expr, $type);
    } else {
      throw Errors::unsupported_binary_operator($expr->get('span'), $op, $lhs, $rhs);
    }
  }

  private static function exit_unary_expr(self $ctx, nodes\UnaryExpr $expr): void {
    $rhs = $ctx->get_type_for_expr($expr->right);
    $op  = $expr->op;
    if ($type = $rhs->apply($op)) {
      $ctx->set_type_for_expr($expr, $type);
    } else {
      throw Errors::unsupported_unary_operator($expr->get('span'), $op, $rhs);
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

      throw Errors::mismatched_list_element_types(
        $element_expr->get('span'),
        $unified_type,
        $index + 1,
        $element_type
      );
    }

    $type = new ListType($unified_type);
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function exit_variant_constructor_expr(self $ctx, nodes\VariantConstructorExpr $expr): void {
    $variant_symbol = $ctx->get_symbol_for_name($expr->ref->tail_segment);
    assert($variant_symbol instanceof names\RefSymbol);
    $union_symbol = $variant_symbol->parent;
    $union_type   = $union_symbol->get('type');

    if ($union_type === null) {
      $span = end($expr->ref->head_segments)->get('span');
      throw Errors::constructor_on_non_type($span);
    } else if (($union_type instanceof UnionType) === false) {
      $span = end($expr->ref->head_segments)->get('span');
      throw Errors::constructor_on_non_union_type($span, $union_type);
    }

    assert($union_type instanceof UnionType);

    $variant_name = $expr->ref->tail_segment->value;
    if ($union_type->has_variant_named($variant_name)) {
      if ($expr->fields instanceof nodes\NamedVariantConstructorFields) {
        $mapping = [];
        foreach ($expr->fields->pairs as $field) {
          $mapping[$field->name->value] = $ctx->get_type_for_expr($field->expr);
        }
        $arg_type = new NamedConstructorFields($mapping);
      } else if ($expr->fields instanceof nodes\OrderedVariantConstructorFields) {
        $order = [];
        foreach ($expr->fields->order as $child_expr) {
          $order[] = $ctx->get_type_for_expr($child_expr);
        }
        $arg_type = new OrderedConstructorFields($order);
      } else {
        $arg_type = new UnitConstructorFields();
      }
      $param_type = $union_type->get_variant_fields($variant_name);
      if ($param_type->accepts_constructor($arg_type)) {
        $params = [];
        $args   = [];
        if ($param_type instanceof NamedVariantFields) {
          foreach (array_keys($param_type->mapping) as $name) {
            $params[] = $param_type->mapping[$name];
            $args[]   = $arg_type->mapping[$name];
          }
        } else if ($param_type instanceof OrderedVariantFields) {
          $params = $param_type->order;
          $args   = $arg_type->order;
        }

        $inferences          = self::infer_stuff($params, $args);
        $concrete_union_type = $union_type->bind_parameters($inferences);
        $ctx->set_type_for_expr($expr, $concrete_union_type);
      } else {
        $span = $expr->get('span');
        throw Errors::wrong_constructor_arguments($span, $variant_name, $param_type, $arg_type);
      }
    } else {
      $span = $expr->ref->tail_segment->get('span');
      throw Errors::no_variant_with_name($span, $union_type, $variant_name);
    }
  }

  /**
   * @param Type[] $params
   * @param Type[] $args
   * @return Type[]
   * @throws Exception
   */
  private static function infer_stuff(array $params, array $args): array {
    assert(count($params) === count($args));
    $inferences = [];
    $queue      = array_map(null, $params, $args);
    while ([ $param, $arg ] = array_shift($queue)) {
      assert($param instanceof Type);
      assert($arg instanceof Type);

      $param = $param->unwrap();
      $arg   = $arg->unwrap();
      assert($param->accepts_as_parameter($arg));

      if ($param instanceof ParamType) {
        $id = $param->symbol->get_id();
        if (array_key_exists($id, $inferences)) {
          $prev_solution = $inferences[$id];
          assert($prev_solution instanceof Type);
          if (!($inferences[$id] = $prev_solution->unify($arg))) {
            throw new Exception("unable to unify $prev_solution and $arg");
          }
        } else {
          $inferences[$id] = $arg;
        }
      } else if ($param instanceof UnionType) {
        assert($arg instanceof UnionType);
        array_push($queue, ...array_map(null, $param->params, $arg->params));
      } else if ($param instanceof ListType) {
        assert($arg instanceof ListType);
        if (!$param->is_empty() && !$arg->is_empty()) {
          array_push($queue, [ $param->element, $arg->element ]);
        }
      } else if ($param instanceof FuncType) {
        assert($arg instanceof FuncType);
        array_push($queue, ...array_map(null, $param->inputs, $arg->inputs));
        array_push($queue, [ $param->output, $arg->output ]);
      }
    }

    return $inferences;
  }

  private static function ref_expr(self $ctx, nodes\RefExpr $expr): void {
    $symbol = $ctx->get_symbol_for_name($expr->ref->tail_segment);
    $type   = $ctx->get_type_for_symbol($symbol);
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function str_literal(self $ctx, nodes\StrLiteral $expr): void {
    $type = new StrType();
    $ctx->set_type_for_expr($expr, $type);
  }

  private static function float_literal(self $ctx, nodes\FloatLiteral $expr): void {
    $type = new FloatType();
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
        return self::unit_note_to_type();
      case $note instanceof nodes\ListNote:
        return self::list_note_to_type($ctx, $note);
      case $note instanceof nodes\ParamNote:
        return self::param_note_to_type($ctx, $note);
      case $note instanceof nodes\ParameterizedNote:
        return self::parameterized_note_to_type($ctx, $note);
      default:
        die('cannot type-check unknown note node: ' . get_class($note));
    }
  }

  private static function func_note_to_type(self $ctx, nodes\FuncNote $note): Type {
    $inputs = [];
    foreach ($note->inputs as $input) {
      $inputs[] = self::note_to_type($ctx, $input);
    }
    $output = self::note_to_type($ctx, $note->output);
    return new FuncType($inputs, $output);
  }

  private static function name_note_to_type(self $ctx, nodes\NameNote $note): Type {
    $symbol = $ctx->get_symbol_for_name($note->ref->tail_segment);
    return $ctx->get_type_for_symbol($symbol);
  }

  private static function unit_note_to_type(): Type {
    return new UnitType();
  }

  private static function list_note_to_type(self $ctx, nodes\ListNote $note): Type {
    $elements = $note->elements
      ? self::note_to_type($ctx, $note->elements)
      : null;
    return new ListType($elements);
  }

  private static function param_note_to_type(self $ctx, nodes\ParamNote $note): Type {
    $symbol = $ctx->get_symbol_for_name($note->name);
    assert($symbol instanceof names\TypeSymbol);
    return new ParamType($symbol, $note->name, null);
  }

  private static function parameterized_note_to_type(self $ctx, nodes\ParameterizedNote $note): Type {
    // TODO
    // 1. resolve root to type
    // 2. resolve parameters to types
    // 3. create new type

    $inner_type = self::note_to_type($ctx, $note->inner);
    if (($inner_type instanceof TypeSupportingParameters) === false) {
      throw new Exception("cannot parameterize the type `$inner_type`");
    }

    assert($inner_type instanceof UnionType);
    $wanted_total_params = $inner_type->total_parameters();
    $found_total_params  = count($note->params);
    if ($inner_type->total_parameters() !== count($note->params)) {
      throw new Exception("expected $wanted_total_params parameters, found $found_total_params");
    }

    $replacements = [];
    foreach ($inner_type->params as $index => $param_type) {
      $replacements[$param_type->symbol->get_id()] = self::note_to_type($ctx, $note->params[$index]);
    }

    return $inner_type->bind_parameters($replacements);
  }
}
