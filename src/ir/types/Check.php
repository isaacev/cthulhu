<?php

namespace Cthulhu\ir\types;

use Cthulhu\Errors\Error;
use Cthulhu\ir;
use Cthulhu\ir\names;
use Cthulhu\ir\nodes;

class Check {
  private array $return_types = [];

  /**
   * @param nodes\Name $name
   * @return names\Symbol
   */
  private function get_symbol_for_name(nodes\Name $name): names\Symbol {
    if ($symbol = $name->get('symbol')) {
      return $symbol;
    }
    die('no known symbol for name');
  }

  /**
   * @param names\Symbol $symbol
   * @return Type
   */
  private function get_type_for_symbol(names\Symbol $symbol): Type {
    if ($type = $symbol->get('type')) {
      return $type;
    }
    die('symbol has not been type-checked yet');
  }

  /**
   * @param names\Symbol $symbol
   * @param Type         $type
   */
  private function set_type_for_symbol(names\Symbol $symbol, Type $type): void {
    $symbol->set('type', $type);
  }

  /**
   * @param nodes\Expr $expr
   * @return Type
   */
  private function get_type_for_expr(nodes\Expr $expr): Type {
    if ($type = $expr->get('type')) {
      return $type;
    }

    $span = $expr->get('span');
    $line = $span->from->line;
    $file = $span->from->file->filepath;
    die("expression has not been type-checked yet on line $line in $file");
  }

  /**
   * @param nodes\Expr $expr
   * @param Type       $type
   */
  private function set_type_for_expr(nodes\Expr $expr, Type $type): void {
    $expr->set('type', $type);
  }

  /**
   * @param nodes\Program $prog
   * @throws Error
   * @noinspection PhpDocRedundantThrowsInspection
   */
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
      'UnitExpr' => function (nodes\UnitExpr $expr) use ($ctx) {
        self::unit_expr($ctx, $expr);
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
          die('missing type binding for an expression');
        }
      },
    ]);
  }

  /**
   * @param Check          $ctx
   * @param nodes\FuncHead $head
   * @throws Error
   */
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
      $ctx->set_type_for_symbol($symbol, Type::freeze_free_types($type));
    }
    $output = self::note_to_type($ctx, $head->output);
    array_push($ctx->return_types, Type::freeze_free_types($output));
    $type   = FuncType::from_input_array($inputs, $output);
    $symbol = $ctx->get_symbol_for_name($head->name);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  /**
   * @param Check          $ctx
   * @param nodes\FuncItem $item
   * @throws Error
   */
  private static function exit_func_item(self $ctx, nodes\FuncItem $item): void {
    $expected_return_type = array_pop($ctx->return_types);
    assert($expected_return_type instanceof Type);

    $block_type = $ctx->get_type_for_expr($item->body);
    $block_type = Type::replace_unknowns($block_type, $expected_return_type);
    if ($expected_return_type->equals($block_type) === false) {
      $span = end($item->body->stmts)->get('span');
      throw Errors::wrong_return_type($span, $expected_return_type, $block_type);
    }
  }

  /**
   * @param Check                $ctx
   * @param nodes\NativeFuncItem $item
   * @throws Error
   */
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
    $type   = FuncType::from_input_array($inputs, $output);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  /**
   * @param Check                $ctx
   * @param nodes\NativeTypeItem $item
   */
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
        die('unreachable');
    }

    $ctx->set_type_for_symbol($item->name->get('symbol'), $type);
  }

  /**
   * @param Check           $ctx
   * @param nodes\UnionItem $item
   * @throws Error
   */
  private static function exit_union_item(self $ctx, nodes\UnionItem $item): void {
    $params = [];
    foreach ($item->params as $param) {
      $params[] = self::param_note_to_type($ctx, $param);
    }

    $variants = [];
    foreach ($item->variants as $variant) {
      if ($variant instanceof nodes\NamedVariantDeclNode) {
        $mapping = [];
        foreach ($variant->fields as $field) {
          $mapping[$field->name->value] = self::note_to_type($ctx, $field->note);
        }
        $variants[$variant->name->value] = new NamedVariant($mapping);
      } else if ($variant instanceof nodes\OrderedVariantDeclNode) {
        $order = [];
        foreach ($variant->members as $member) {
          $order[] = self::note_to_type($ctx, $member);
        }
        $variants[$variant->name->value] = new OrderedVariant($order);
      } else {
        $variants[$variant->name->value] = new UnitVariant();
      }
    }

    $pointer = new UnionType($variants);
    $symbol  = $item->name->get('symbol');
    assert($symbol instanceof names\RefSymbol);
    $ref   = self::build_ref_from_symbol($symbol);
    $alias = new NamedType($ref, $params, $pointer);
    $ctx->set_type_for_symbol($symbol, $alias);
  }

  /**
   * @param names\RefSymbol $tail_symbol
   * @return nodes\Ref
   */
  static function build_ref_from_symbol(names\RefSymbol $tail_symbol): nodes\Ref {
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

  /**
   * @param Check         $ctx
   * @param nodes\LetStmt $stmt
   * @throws Error
   */
  private static function exit_let_stmt(self $ctx, nodes\LetStmt $stmt): void {
    $symbol    = $ctx->get_symbol_for_name($stmt->name);
    $expr_type = $ctx->get_type_for_expr($stmt->expr);

    if ($stmt->note !== null) {
      $note_type = self::note_to_type($ctx, $stmt->note);
      $expr_type = Type::replace_unknowns($expr_type, $note_type);
      if ($note_type->equals($expr_type) === false) {
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

  /**
   * @param Check          $ctx
   * @param nodes\MatchArm $arm
   * @param ir\Path        $path
   * @throws Error
   */
  private static function enter_match_arm(self $ctx, nodes\MatchArm $arm, ir\Path $path): void {
    $match_expr = $path->parent->node;
    assert($match_expr instanceof nodes\MatchExpr);
    $disc_type = $ctx->get_type_for_expr($match_expr->disc->expr);
    $pattern   = $arm->pattern;
    self::check_pattern($ctx, $pattern, $disc_type);
  }

  /**
   * @param Check         $ctx
   * @param nodes\Pattern $pattern
   * @param Type          $type
   * @throws Error
   */
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
        self::check_str_pattern($pattern, $type);
        break;
      case $pattern instanceof nodes\FloatConstPattern:
        self::check_float_pattern($pattern, $type);
        break;
      case $pattern instanceof nodes\IntConstPattern:
        self::check_int_pattern($pattern, $type);
        break;
      case $pattern instanceof nodes\BoolConstPattern:
        self::check_bool_pattern($pattern, $type);
        break;
      default:
        die('unreachable');
    }
  }

  /**
   * @param Check                $ctx
   * @param nodes\VariantPattern $pattern
   * @param NamedType            $discriminant_alias
   * @throws Error
   */
  private static function check_variant_pattern(self $ctx, nodes\VariantPattern $pattern, NamedType $discriminant_alias): void {
    $discriminant_union = $discriminant_alias->pointer;
    assert($discriminant_union instanceof UnionType);

    $pattern_variant_symbol = $ctx->get_symbol_for_name($pattern->ref->tail_segment);
    assert($pattern_variant_symbol instanceof names\RefSymbol);
    $pattern_variant_name = $pattern->ref->tail_segment->value;
    $pattern_alias_symbol = $pattern_variant_symbol->parent;
    $pattern_alias        = $ctx->get_type_for_symbol($pattern_alias_symbol);
    assert($pattern_alias instanceof NamedType);
    $pattern_union = $pattern_alias->pointer;
    assert($pattern_union instanceof UnionType);

    $fields    = $pattern->fields;
    $arguments = $discriminant_union->variants[$pattern_variant_name];

    if ($arguments instanceof NamedVariant) {
      if (($fields instanceof nodes\NamedVariantPatternFields) === false) {
        throw new \Exception("expected pattern in the form of $arguments, instead found $fields");
      }
      foreach ($fields->mapping as $field) {
        self::check_pattern($ctx, $field->pattern, $arguments->mapping[$field->name->value]);
      }
    } else if ($arguments instanceof OrderedVariant) {
      if (($fields instanceof nodes\OrderedVariantPatternFields) === false) {
        throw new \Exception("expected pattern in the form of $arguments, instead found $fields");
      }
      foreach ($fields->order as $index => $field) {
        self::check_pattern($ctx, $field->pattern, $arguments->order[$index]);
      }
    } else {
      if ($fields !== null) {
        throw new \Exception("expected pattern in the form of $arguments");
      }
      assert($fields === null);
    }
  }

  /**
   * @param Check                 $ctx
   * @param nodes\WildcardPattern $pattern
   * @param Type                  $type
   */
  private static function check_wildcard_pattern(self $ctx, nodes\WildcardPattern $pattern, Type $type): void {
    // do nothing
  }

  /**
   * @param Check                 $ctx
   * @param nodes\VariablePattern $pattern
   * @param Type                  $type
   */
  private static function check_variable_pattern(self $ctx, nodes\VariablePattern $pattern, Type $type): void {
    $symbol = $ctx->get_symbol_for_name($pattern->name);
    $ctx->set_type_for_symbol($symbol, $type);
  }

  /**
   * @param nodes\StrConstPattern $pattern
   * @param Type                  $type
   * @throws Error
   */
  private static function check_str_pattern(nodes\StrConstPattern $pattern, Type $type): void {
    if (StrType::matches($type) === false) {
      throw Errors::incompatible_pattern($pattern->get('span'), $type);
    }
  }

  /**
   * @param nodes\FloatConstPattern $pattern
   * @param Type                    $type
   * @throws Error
   */
  private static function check_float_pattern(nodes\FloatConstPattern $pattern, Type $type): void {
    if (FloatType::matches($type) === false) {
      throw Errors::incompatible_pattern($pattern->get('span'), $type);
    }
  }

  /**
   * @param nodes\IntConstPattern $pattern
   * @param Type                  $type
   * @throws Error
   */
  private static function check_int_pattern(nodes\IntConstPattern $pattern, Type $type): void {
    if (IntType::matches($type) === false) {
      throw Errors::incompatible_pattern($pattern->get('span'), $type);
    }
  }

  /**
   * @param nodes\BoolConstPattern $pattern
   * @param Type                   $type
   * @throws Error
   */
  private static function check_bool_pattern(nodes\BoolConstPattern $pattern, Type $type): void {
    if (BoolType::matches($type) === false) {
      throw Errors::incompatible_pattern($pattern->get('span'), $type);
    }
  }

  /**
   * @param Check           $ctx
   * @param nodes\MatchExpr $expr
   * @throws Error
   */
  private static function exit_match_expr(self $ctx, nodes\MatchExpr $expr): void {
    assert(empty($expr->arms) === false);

    $match_type = $ctx->get_type_for_expr($expr->arms[0]->handler->stmt->expr);
    foreach (array_slice($expr->arms, 1) as $arm) {
      $arm_type                  = $ctx->get_type_for_expr($arm->handler->stmt->expr);
      $arm_type_without_unknowns = Type::replace_unknowns($arm_type, $match_type);
      if ($match_type->equals($arm_type_without_unknowns) === false) {
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

  /**
   * @param Check        $ctx
   * @param nodes\IfExpr $expr
   * @throws Error
   */
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

    $if_true_type  = Type::replace_unknowns($if_true_type, $if_false_type);
    $if_false_type = Type::replace_unknowns($if_false_type, $if_true_type);
    if ($if_true_type->equals($if_false_type)) {
      $ctx->set_type_for_expr($expr, $if_true_type);
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

  /**
   * @param Check          $ctx
   * @param nodes\CallExpr $expr
   * @throws Error
   */
  private static function exit_call_expr(self $ctx, nodes\CallExpr $expr): void {
    $callee_type = $expr->callee->get('type');

    if (empty($expr->args)) {
      if (($callee_type instanceof FuncType) === false) {
        throw Errors::call_to_non_function($expr->get('span'), $callee_type);
      }
      $replacements = Type::infer_free_types($callee_type->input, new UnitType(), $expr->get('span'));
      $callee_type  = Type::replace_free_types($callee_type->output, $replacements);
    } else {
      foreach ($expr->args as $index => $arg_expr) {
        if (($callee_type instanceof FuncType) === false) {
          throw Errors::call_to_non_function($arg_expr->get('span'), $callee_type);
        }
        $replacements = Type::infer_free_types($callee_type->input, $arg_expr->get('type'), $arg_expr->get('span'));
        $callee_type  = Type::replace_free_types($callee_type->output, $replacements);
      }
    }

    $ctx->set_type_for_expr($expr, $callee_type);
  }

  /**
   * @param Check            $ctx
   * @param nodes\BinaryExpr $expr
   * @throws Error
   */
  private static function exit_binary_expr(self $ctx, nodes\BinaryExpr $expr): void {
    $lhs = $ctx->get_type_for_expr($expr->left);
    $rhs = $ctx->get_type_for_expr($expr->right);
    $op  = $expr->op;
    if ($type = $lhs->apply_operator($op, $rhs)) {
      $ctx->set_type_for_expr($expr, $type);
    } else {
      throw Errors::unsupported_binary_operator($expr->get('span'), $op, $lhs, $rhs);
    }
  }

  /**
   * @param Check           $ctx
   * @param nodes\UnaryExpr $expr
   * @throws Error
   */
  private static function exit_unary_expr(self $ctx, nodes\UnaryExpr $expr): void {
    $rhs = $ctx->get_type_for_expr($expr->right);
    $op  = $expr->op;
    if ($type = $rhs->apply_operator($op)) {
      $ctx->set_type_for_expr($expr, $type);
    } else {
      throw Errors::unsupported_unary_operator($expr->get('span'), $op, $rhs);
    }
  }

  /**
   * @param Check          $ctx
   * @param nodes\ListExpr $expr
   * @throws Error
   */
  private static function exit_list_expr(self $ctx, nodes\ListExpr $expr): void {
    $unified_type = new UnknownType();
    foreach ($expr->elements as $index => $element_expr) {
      $element_type = $ctx->get_type_for_expr($element_expr);
      $element_type = Type::replace_unknowns($element_type, $unified_type);
      $unified_type = Type::replace_unknowns($unified_type, $element_type);
      if ($unified_type->equals($element_type) === false) {
        throw Errors::mismatched_list_element_types(
          $element_expr->get('span'),
          $unified_type,
          $index + 1,
          $element_type
        );
      }
    }

    $type = new ListType($unified_type);
    $ctx->set_type_for_expr($expr, $type);
  }

  /**
   * @param Check                        $ctx
   * @param nodes\VariantConstructorExpr $expr
   * @throws Error
   */
  private static function exit_variant_constructor_expr(self $ctx, nodes\VariantConstructorExpr $expr): void {
    $variant_symbol = $ctx->get_symbol_for_name($expr->ref->tail_segment);
    assert($variant_symbol instanceof names\RefSymbol);
    $alias_symbol = $variant_symbol->parent;
    $alias_type   = $alias_symbol->get('type');

    /**
     * Make sure that the name used in the constructor points to a union type
     */
    if ($alias_type === null) {
      $span = end($expr->ref->head_segments)->get('span');
      throw Errors::constructor_on_non_type($span);
    } else if (
      ($alias_type instanceof NamedType) === false ||
      ($alias_type->pointer instanceof UnionType) === false
    ) {
      $span = end($expr->ref->head_segments)->get('span');
      throw Errors::constructor_on_non_union_type($span, $alias_type);
    }

    assert($alias_type instanceof NamedType);
    $union_type = $alias_type->pointer;
    assert($union_type instanceof UnionType);

    /**
     * Make sure that the union type has a variant with the same name as the constructor
     */
    $variant_name = $expr->ref->tail_segment->value;
    if ($union_type->has_variant_named($variant_name) === false) {
      throw Errors::no_variant_with_name($expr->ref->tail_segment->get('span'), $alias_type, $variant_name);
    }

    /**
     * Based on what types have been supplied to the constructor, derive as
     * many of the type parameters as possible. Any type parameters that can't
     * be derived are replaced with the `UnknownType`.
     */
    $replacements = [];
    $param_fields = $union_type->variants[$variant_name];
    if ($expr->fields instanceof nodes\NamedVariantConstructorFields) {
      assert($param_fields instanceof NamedVariant);
      foreach ($expr->fields->pairs as $field) {
        $param_type = $param_fields->mapping[$field->name->value];
        $arg_type   = $ctx->get_type_for_expr($field->expr);
        $span       = $field->get('span');
        Type::infer_free_types($param_type, $arg_type, $span, $replacements);
        $arg_type   = Type::replace_free_types($arg_type, $replacements);
        $param_type = Type::replace_free_types($param_type, $replacements);
        if ($param_type->equals($arg_type) === false) {
          throw Errors::wrong_constructor_argument($field->expr->get('span'), $variant_name, $arg_type, $param_type);
        }
      }
    } else if ($expr->fields instanceof nodes\OrderedVariantConstructorFields) {
      assert($param_fields instanceof OrderedVariant);
      foreach ($expr->fields->order as $index => $child_expr) {
        $param_type = $param_fields->order[$index];
        $arg_type   = $ctx->get_type_for_expr($child_expr);
        $span       = $child_expr->get('span');
        Type::infer_free_types($param_type, $arg_type, $span, $replacements);
        $arg_type   = Type::replace_free_types($arg_type, $replacements);
        $param_type = Type::replace_free_types($param_type, $replacements);
        if ($param_type->equals($arg_type) === false) {
          throw Errors::wrong_constructor_argument($child_expr->get('span'), $variant_name, $arg_type, $param_type);
        }
      }
    } else {
      assert($param_fields instanceof UnitVariant);
    }

    $solved_type = Type::replace_free_types($alias_type, $replacements);
    $solved_type = Type::replace_free_types_with_unknown($solved_type);
    $ctx->set_type_for_expr($expr, $solved_type);
  }

  /**
   * @param Check         $ctx
   * @param nodes\RefExpr $expr
   */
  private static function ref_expr(self $ctx, nodes\RefExpr $expr): void {
    $symbol = $ctx->get_symbol_for_name($expr->ref->tail_segment);
    $type   = $ctx->get_type_for_symbol($symbol);
    $ctx->set_type_for_expr($expr, $type);
  }

  /**
   * @param Check            $ctx
   * @param nodes\StrLiteral $expr
   */
  private static function str_literal(self $ctx, nodes\StrLiteral $expr): void {
    $type = new StrType();
    $ctx->set_type_for_expr($expr, $type);
  }

  /**
   * @param Check              $ctx
   * @param nodes\FloatLiteral $expr
   */
  private static function float_literal(self $ctx, nodes\FloatLiteral $expr): void {
    $type = new FloatType();
    $ctx->set_type_for_expr($expr, $type);
  }

  /**
   * @param Check            $ctx
   * @param nodes\IntLiteral $expr
   */
  private static function int_literal(self $ctx, nodes\IntLiteral $expr): void {
    $type = new IntType();
    $ctx->set_type_for_expr($expr, $type);
  }

  /**
   * @param Check             $ctx
   * @param nodes\BoolLiteral $expr
   */
  private static function bool_literal(self $ctx, nodes\BoolLiteral $expr): void {
    $type = new BoolType();
    $ctx->set_type_for_expr($expr, $type);
  }

  /**
   * @param Check          $ctx
   * @param nodes\UnitExpr $expr
   */
  private static function unit_expr(self $ctx, nodes\UnitExpr $expr): void {
    $type = new UnitType();
    $ctx->set_type_for_expr($expr, $type);
  }

  /**
   * @param Check       $ctx
   * @param nodes\Block $block
   */
  private static function exit_block(self $ctx, nodes\Block $block): void {
    $type = $ctx->get_type_for_expr(end($block->stmts)->expr);
    $ctx->set_type_for_expr($block, $type);
  }

  /**
   * @param Check      $ctx
   * @param nodes\Note $note
   * @return Type
   * @throws Error
   */
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

  /**
   * @param Check          $ctx
   * @param nodes\FuncNote $note
   * @return Type
   * @throws Error
   */
  private static function func_note_to_type(self $ctx, nodes\FuncNote $note): Type {
    $inputs = [];
    foreach ($note->inputs as $input) {
      $inputs[] = self::note_to_type($ctx, $input);
    }
    $output = self::note_to_type($ctx, $note->output);
    return FuncType::from_input_array($inputs, $output);
  }

  /**
   * @param Check          $ctx
   * @param nodes\NameNote $note
   * @return Type
   */
  private static function name_note_to_type(self $ctx, nodes\NameNote $note): Type {
    $symbol = $ctx->get_symbol_for_name($note->ref->tail_segment);
    return $ctx->get_type_for_symbol($symbol);
  }

  /**
   * @return Type
   */
  private static function unit_note_to_type(): Type {
    return new UnitType();
  }

  /**
   * @param Check          $ctx
   * @param nodes\ListNote $note
   * @return Type
   * @throws Error
   */
  private static function list_note_to_type(self $ctx, nodes\ListNote $note): Type {
    $elements = $note->elements
      ? self::note_to_type($ctx, $note->elements)
      : null;
    return new ListType($elements);
  }

  /**
   * @param Check           $ctx
   * @param nodes\ParamNote $note
   * @return FreeType
   */
  private static function param_note_to_type(self $ctx, nodes\ParamNote $note): FreeType {
    $symbol = $ctx->get_symbol_for_name($note->name);
    assert($symbol instanceof names\TypeSymbol);
    return new FreeType($symbol, $note->name);
  }

  /**
   * @param Check                   $ctx
   * @param nodes\ParameterizedNote $note
   * @return Type
   * @throws Error
   */
  private static function parameterized_note_to_type(self $ctx, nodes\ParameterizedNote $note): Type {
    $inner_type = self::note_to_type($ctx, $note->inner);
    if (($inner_type instanceof NamedType) === false) {
      throw Errors::type_does_not_support_parameters($note->get('span'), $inner_type);
    }

    assert($inner_type instanceof NamedType);
    $wanted_total_params = count($inner_type->params);
    $found_total_params  = count($note->params);
    if ($wanted_total_params !== $found_total_params) {
      throw Errors::wrong_num_type_parameters($note->get('span'), $inner_type, $wanted_total_params, $found_total_params);
    }

    $new_params   = [];
    $replacements = [];
    foreach ($note->params as $index => $param_note) {
      $new_params[$index] = $param_type = self::note_to_type($ctx, $param_note);
      $inner_param        = $inner_type->params[$index];
      assert($inner_param instanceof FreeType);
      $replacements[$inner_param->symbol->get_id()] = $param_type;
    }

    $new_pointer = Type::replace_free_types($inner_type->pointer, $replacements);
    return new NamedType($inner_type->ref, $new_params, $new_pointer);

//    $inner_type = self::note_to_type($ctx, $note->inner);
//    if (($inner_type instanceof TypeSupportingParameters) === false) {
//      throw Errors::type_does_not_support_parameters($note->get('span'), $inner_type);
//    }
//
//    assert($inner_type instanceof UnionType);
//    $wanted_total_params = $inner_type->total_parameters();
//    $found_total_params  = count($note->params);
//    if ($inner_type->total_parameters() !== count($note->params)) {
//      throw Errors::wrong_num_type_parameters($note->get('span'), $inner_type, $wanted_total_params, $found_total_params);
//    }
//
//    $replacements = [];
//    foreach ($inner_type->params as $index => $param_type) {
//      $replacements[$param_type->symbol->get_id()] = self::note_to_type($ctx, $note->params[$index]);
//    }
//
//    return $inner_type->bind_parameters($replacements);
  }
}
