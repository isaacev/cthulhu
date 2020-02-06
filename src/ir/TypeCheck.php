<?php

namespace Cthulhu\ir;

use Cthulhu\ast\nodes as ast;
use Cthulhu\err\Error;
use Cthulhu\ir\names\Binding;
use Cthulhu\ir\types\ParameterContext;
use Cthulhu\lib\trees\Path;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;
use Cthulhu\val;

/**
 * Given a syntax tree and any builtin type bindings, ensure that the syntax
 * tree respects all of the type rules defined by the language. Attach a type
 * to all symbols, expressions, and statements that are successfully checked.
 *
 * The type information attached to the syntax tree can be used later to build
 * a fully-typed intermediate representation (IR). Because the IR has type
 * information about all of its nodes, optimizations that are applied to the IR
 * can be type-checked. This reduces the likelihood that bugs in the optimization
 * pass accidentally violate the program's semantics.
 *
 * The type-checking algorithm uses type inference inside of function bodies but
 * expects all function signatures to be explicitly typed. The inference rules
 * draw heavily on Algorithm W which is an implementation of the Hindley-Milner
 * type system.
 *
 * @link https://en.wikipedia.org/wiki/Hindley%E2%80%93Milner_type_system
 * @link http://dysphoria.net/2009/06/28/hindley-milner-type-inference-in-scala
 */
class TypeCheck {
  public const TYPE_KEY = 'type2';

  /**
   * @param Binding[]   $bindings
   * @param ast\Program $tree
   */
  public static function syntax_tree(array $bindings, ast\Program $tree): void {
    /* @var types\Type[] $return_stack */
    $return_stack = [];

    foreach ($bindings as $binding) {
      $binding->symbol->set(self::TYPE_KEY, new types\Atomic($binding->name));
    }

    Visitor::walk($tree, [
      'enter(IntrinsicSignature)' => function (ast\IntrinsicSignature $sig) {
        $free_inputs = [];
        if ($sig->params instanceof ast\TupleNote) {
          foreach ($sig->params->members as $note) {
            $free_inputs[] = self::note_to_type($note, true);
          }
        } else {
          $free_inputs[] = self::note_to_type($sig->params, true);
        }

        $free_output = self::note_to_type($sig->returns, true);
        $free_type   = types\Func::from_input_array($free_inputs, $free_output);
        $sig->name->get('symbol')->set(self::TYPE_KEY, $free_type);
      },

      'EnumItem' => function (ast\EnumItem $enum) {
        $free_params = [];
        foreach ($enum->params as $note) {
          $free_params[] = self::note_to_type($note, true);
        }

        $free_forms = [];
        foreach ($enum->forms as $form) {
          if ($form instanceof ast\NamedFormDecl) {
            $fields = [];
            foreach ($form->params as $pair) {
              $field_name          = $pair->name->value;
              $field_type          = self::note_to_type($pair->note, true);
              $fields[$field_name] = $field_type;
            }
            $free_forms[$form->name->value] = new types\Record($fields);
          } else if ($form instanceof ast\OrderedFormDecl) {
            $members = [];
            foreach ($form->params as $note) {
              $member_type = self::note_to_type($note, true);
              $members[]   = $member_type;
            }
            $free_forms[$form->name->value] = new types\Tuple($members);
          } else {
            $free_forms[$form->name->value] = types\Atomic::unit();
          }
        }

        $enum_name = $enum->name->get('symbol')->__toString();
        $enum_type = new types\Enum($enum_name, $free_params, $free_forms);
        $enum->name->get('symbol')->set(self::TYPE_KEY, $enum_type);
      },

      'enter(FnItem)' => function (ast\FnItem $item) use (&$return_stack) {
        $free_inputs = [];
        foreach ($item->params->params as $param) {
          $free_inputs[] = self::note_to_type($param->note, true);
          $fixed_type    = self::note_to_type($param->note, false);
          $param->name->get('symbol')->set(self::TYPE_KEY, $fixed_type);
        }

        $free_output  = self::note_to_type($item->returns, true);
        $fixed_output = self::note_to_type($item->returns, false);
        array_push($return_stack, $fixed_output);

        $free_type = types\Func::from_input_array($free_inputs, $free_output);
        $item->name->get('symbol')->set(self::TYPE_KEY, $free_type);
      },
      'exit(FnItem)' => function (ast\FnItem $item) use (&$return_stack) {

        $sig_type = array_pop($return_stack);
        $ret_type = $item->body->get(self::TYPE_KEY);

        try {
          self::unify($sig_type, $ret_type);
        } catch (types\UnificationFailure $failure) {
          $ret_span = self::block_ret_span($item->body);
          $sig_span = $item->returns->get('span');
          throw Errors::wrong_ret_type($ret_span, $sig_span, $ret_type, $sig_type);
        }
      },

      'enter(ClosureExpr)' => function (ast\ClosureExpr $expr) {
        foreach ($expr->params->params as $index => $name) {
          $type_name = chr(ord('a') + $index);
          $free_type = new types\FreeTypeVar($type_name, null);
          $name->get('symbol')->set(self::TYPE_KEY, $free_type);
        }
      },
      'exit(ClosureExpr)' => function (ast\ClosureExpr $expr) {
        $input_types = [];
        foreach ($expr->params->params as $name) {
          $input_types[] = $input_type = $name->get('symbol')->get(self::TYPE_KEY);
          assert($input_type instanceof types\Type);
        }

        if (empty($expr->body->stmts)) {
          $return_type = types\Atomic::unit();
        } else {
          $return_type = end($expr->body->stmts)->get(self::TYPE_KEY);
        }

        $type = types\Func::from_input_array($input_types, $return_type);
        $expr->set(self::TYPE_KEY, $type);
      },

      'exit(LetStmt)' => function (ast\LetStmt $stmt) {
        $expr_type = $stmt->expr->get(self::TYPE_KEY);

        if ($stmt->note) {
          $note_type = self::note_to_type($stmt->note, false);

          try {
            self::unify($note_type, $expr_type);
          } catch (types\UnificationFailure $failure) {
            $note_span = $stmt->note->get('span');
            $expr_span = $stmt->expr->get('span');
            throw Errors::wrong_let_type($note_span, $note_type, $expr_span, $expr_type);
          }
        }

        $stmt->name->get('symbol')->set(self::TYPE_KEY, $expr_type);
        $type = types\Atomic::unit();
        $stmt->set(self::TYPE_KEY, $type);
      },

      'exit(SemiStmt)' => function (ast\SemiStmt $stmt) {
        $type = types\Atomic::unit();
        $stmt->set(self::TYPE_KEY, $type);
      },

      'exit(ExprStmt)' => function (ast\ExprStmt $stmt) {
        $type = $stmt->expr->get(self::TYPE_KEY);
        $stmt->set(self::TYPE_KEY, $type);
      },

      'exit(BlockNode)' => function (ast\BlockNode $block) {
        if (empty($block->stmts)) {
          $type = types\Atomic::unit();
        } else {
          $type = end($block->stmts)->get(self::TYPE_KEY);
        }

        $block->set(self::TYPE_KEY, $type);
      },

      'exit(IfExpr)' => function (ast\IfExpr $expr) {
        $cond_type = $expr->condition->get(self::TYPE_KEY);
        if (!($cond_type instanceof types\Atomic) || $cond_type->name !== 'Bool') {
          $cond_span = $expr->condition->get('span');
          throw Errors::wrong_cond_type($cond_span, $cond_type);
        }

        $consequent_type = $expr->consequent->get(self::TYPE_KEY);
        if ($expr->alternate) {
          $alternate_type = $expr->alternate->get(self::TYPE_KEY);
          try {
            self::unify($consequent_type, $alternate_type);
          } catch (types\UnificationFailure $failure) {
            $cons_span = self::block_ret_span($expr->consequent);
            $alt_span  = self::block_ret_span($expr->alternate);
            throw Errors::cons_alt_mismatch($cons_span, $consequent_type, $alt_span, $alternate_type);
          }
        } else {
          try {
            self::unify($consequent_type, types\Atomic::unit());
          } catch (types\UnificationFailure $failure) {
            $cons_span = self::block_ret_span($expr->consequent);
            throw Errors::cons_non_unit($cons_span, $consequent_type);
          }
        }

        $expr->set(self::TYPE_KEY, $consequent_type);
      },

      'enter(MatchArm)' => function (ast\MatchArm $arm, Path $path) {
        $match_expr = $path->parent->node;
        assert($match_expr instanceof ast\MatchExpr);

        $disc_type = $match_expr->discriminant->get(self::TYPE_KEY);
        assert($disc_type instanceof types\Type);

        $pattern_type = self::pattern_to_type($arm->pattern);

        try {
          self::unify($disc_type, $pattern_type);
        } catch (types\UnificationFailure $failure) {
          $pattern_span = $arm->pattern->get('span');
          throw Errors::wrong_pattern_for_type($pattern_span, $pattern_type, $disc_type);
        }
      },

      'exit(MatchArm)' => function (ast\MatchArm $arm, Path $path) {
        $match_expr = $path->parent->node;
        assert($match_expr instanceof ast\MatchExpr);

        $match_type = $match_expr->get(self::TYPE_KEY);
        $arm_type   = $arm->handler->get(self::TYPE_KEY);
        if ($match_type === null) {
          $match_expr->set(self::TYPE_KEY, $arm_type);
        } else {
          try {
            self::unify($match_expr->get(self::TYPE_KEY), $arm_type);
          } catch (types\UnificationFailure $failure) {
            $handler_span = $arm->handler->get('span');
            throw Errors::wrong_arm_type($handler_span, $match_type, $arm_type);
          }
        }
      },

      'exit(VariantConstructorExpr)' => function (ast\VariantConstructorExpr $ctor) {
        $enum_symbol = end($ctor->path->head)->get('symbol');
        $enum_type   = $enum_symbol->get(self::TYPE_KEY);
        $enum_type   = self::fresh($enum_type);
        assert($enum_type instanceof types\Enum);

        $form_name = $ctor->path->tail->value;
        $form_type = $enum_type->forms[$form_name];

        if ($ctor->fields instanceof ast\NamedVariantConstructorFields) {
          $fields = [];
          foreach ($ctor->fields->pairs as $pair) {
            $field_name          = $pair->name->value;
            $field_type          = $pair->expr->get(self::TYPE_KEY);
            $fields[$field_name] = $field_type;
          }
          $arg_type = new types\Record($fields);
        } else if ($ctor->fields instanceof ast\OrderedVariantConstructorFields) {
          $members = [];
          foreach ($ctor->fields->order as $member) {
            $member_type = $member->get(self::TYPE_KEY);
            $members[]   = $member_type;
          }
          $arg_type = new types\Tuple($members);
        } else {
          $arg_type = types\Atomic::unit();
        }

        try {
          self::unify($form_type, $arg_type);
        } catch (types\UnificationFailure $failure) {
          $ctor_span = $ctor->get('span');
          throw Errors::wrong_ctor_args($ctor_span, $form_type, $arg_type);
        }

        $ctor->set(self::TYPE_KEY, $enum_type);
      },

      'exit(CallExpr)' => function (ast\CallExpr $expr) {
        $call_type = $expr->callee->get(self::TYPE_KEY);
        assert($call_type instanceof types\Type);
        $call_type = self::fresh($call_type->flatten());

        if ($call_type instanceof types\Func) {
          if (count($expr->args) === 0) {
            $arg_type = types\Atomic::unit();
            $arg_span = $expr->args->get('span');

            // Perform type unification on the argument type and the callee's
            // first parameter type. This unification has the possibility of
            // changing the callee's return type.
            try {
              self::unify($call_type->input, $arg_type);
            } catch (types\UnificationFailure $err) {
              $sig_type = $call_type->input;
              throw Errors::wrong_arg_type($arg_span, $arg_type, $sig_type);
            }

            $ret_type = $call_type->output;
          } else {
            $call_span = $expr->get('span');
            foreach ($expr->args->exprs as $index => $arg) {
              $call_type = $call_type->prune();
              $arg_type  = $arg->get(self::TYPE_KEY);
              $arg_span  = $arg->get('span');

              if (($call_type->flatten() instanceof types\Func) === false) {
                throw Errors::call_non_func($call_span, $arg_span, $call_type, $arg_type);
              }

              assert($call_type instanceof types\Func);

              // Perform type unification on the argument type and the callee's
              // first parameter type. This unification has the possibility of
              // changing the callee's return type.
              try {
                self::unify($call_type->input, $arg_type);
              } catch (types\UnificationFailure $err) {
                $sig_type = $call_type->input;
                throw Errors::wrong_arg_type($arg_span, $arg_type, $sig_type);
              }

              $call_type = $call_type->output->flatten();
              $call_span = Span::join($call_span, $arg_span);
            }

            $ret_type = $call_type;
          }

          $expr->set(self::TYPE_KEY, $ret_type);
        } else {
          $call_span = $expr->get('span');
          $arg_span  = $expr->args->get('span');
          $arg_type  = types\Atomic::unit();
          throw Errors::call_non_func($call_span, $arg_span, $call_type, $arg_type);
        }
      },

      'exit(BinaryExpr)' => function (ast\BinaryExpr $expr) {
        $left_type  = $expr->left->get(self::TYPE_KEY);
        $right_type = $expr->right->get(self::TYPE_KEY);

        $call_type = self::fresh($expr->operator->get(self::TYPE_KEY));
        assert($call_type instanceof types\Func);
        assert($call_type->output instanceof types\Func);

        try {
          self::unify($call_type->input, $left_type);
        } catch (types\UnificationFailure $failure) {
          $left_span = $expr->left->get('span');
          $sig_type  = $call_type->input;
          throw Errors::wrong_lhs_type($expr->operator->oper, $left_span, $left_type, $sig_type);
        }

        $call_type = $call_type->output;
        try {
          self::unify($call_type->input, $right_type);
        } catch (types\UnificationFailure $failure) {
          $right_span = $expr->right->get('span');
          $sig_type   = $call_type->input;
          throw Errors::wrong_rhs_type($expr->operator->oper, $right_span, $right_type, $sig_type);
        }

        $type = $call_type->output;
        $expr->set(self::TYPE_KEY, $type);
      },

      'OperatorRef' => function (ast\OperatorRef $expr) {
        $type = $expr->oper->get('symbol')->get(self::TYPE_KEY);
        $expr->set(self::TYPE_KEY, $type);
      },

      'exit(UnaryExpr)' => function (ast\UnaryExpr $expr) {
        $right_type = $expr->right->get(self::TYPE_KEY);
        $call_type  = self::fresh($expr->operator->get(self::TYPE_KEY));
        assert($call_type instanceof types\Func);

        try {
          self::unify($call_type->input, $right_type);
        } catch (types\UnificationFailure $failure) {
          $right_span = $expr->right->get('span');
          $sig_type   = $call_type->input;
          throw Errors::wrong_unary_type($expr->operator->oper, $right_span, $right_type, $sig_type);
        }

        $type = $call_type->output;
        $expr->set(self::TYPE_KEY, $type);
      },

      'exit(ListExpr)' => function (ast\ListExpr $expr) {
        $elements_type = new types\FreeTypeVar('_', null);
        foreach ($expr->elements as $index => $elem_expr) {
          $elem_type = $elem_expr->get(self::TYPE_KEY);
          try {
            self::unify($elements_type, $elem_type);
          } catch (types\UnificationFailure $failure) {
            $elem_span = $elem_expr->get('span');
            throw Errors::wrong_elem_type($elem_span, $index + 1, $elem_type, $elements_type);
          }
        }
        $list_type = new types\ListType($elements_type);
        $expr->set(self::TYPE_KEY, $list_type);
      },

      'PathExpr' => function (ast\PathExpr $expr) {
        $type = $expr->path->tail->get('symbol')->get(self::TYPE_KEY);
        $expr->set(self::TYPE_KEY, $type);
      },

      'StrLiteral' => function (ast\StrLiteral $lit) {
        $type = types\Atomic::str();
        $lit->set(self::TYPE_KEY, $type);
      },

      'FloatLiteral' => function (ast\FloatLiteral $lit) {
        $type = types\Atomic::float();
        $lit->set(self::TYPE_KEY, $type);
      },

      'IntLiteral' => function (ast\IntLiteral $lit) {
        $type = types\Atomic::int();
        $lit->set(self::TYPE_KEY, $type);
      },

      'BoolLiteral' => function (ast\BoolLiteral $lit) {
        $type = types\Atomic::bool();
        $lit->set(self::TYPE_KEY, $type);
      },

      'UnitLiteral' => function (ast\UnitLiteral $lit) {
        $type = types\Atomic::unit();
        $lit->set(self::TYPE_KEY, $type);
      },

      'exit(Expr)' => function (ast\Expr $expr, Path $path) {
        if (!$expr->get(self::TYPE_KEY)) {
          die("$path->kind missing a type\n");
        }
      },

      'exit(Stmt)' => function (ast\Stmt $stmt, Path $path) {
        if (!$stmt->get(self::TYPE_KEY)) {
          die("$path->kind missing a type\n");
        }
      },
    ]);
  }

  private static function fresh(types\Type $t): types\Type {
    $ctx = new ParameterContext(null);
    return $t->fresh($ctx);
  }

  /**
   * @param types\Type $t1
   * @param types\Type $t2
   * @throws types\UnificationFailure
   */
  private static function unify(types\Type $t1, types\Type $t2): void {
    $t1 = $t1->prune();
    $t2 = $t2->prune();

    if ($t1 instanceof types\FreeTypeVar) {
      if ($t1 !== $t2) {
        if ($t1->contains($t2)) {
          die("recursive unification between $t1 and $t2\n");
        } else if ($t1->has_instance()) {
          throw new types\UnificationFailure();
        } else {
          $t1->set_instance($t2);
        }
      }
    } else if ($t2 instanceof types\FreeTypeVar) {
      self::unify($t2, $t1);
    } else {
      assert($t1 instanceof types\ConcreteType);
      assert($t2 instanceof types\ConcreteType);

      if ($t1 instanceof types\ListType) {
        if ($t2 instanceof types\ListType) {
          self::unify($t1->elements, $t2->elements);
          return;
        } else {
          throw new types\UnificationFailure();
        }
      }

      if ($t1 instanceof types\Record) {
        if (
          $t2 instanceof types\Record &&
          count($t1) === count($t2) &&
          empty(array_diff_key($t1->fields, $t2->fields))
        ) {
          foreach ($t1->fields as $field_name => $t1_field) {
            $t2_field = $t2->fields[$field_name];
            self::unify($t1_field, $t2_field);
          }
          return;
        } else {
          throw new types\UnificationFailure();
        }
      }

      if ($t1 instanceof types\Enum) {
        if ($t2 instanceof types\Enum && $t1->name === $t2->name) {
          for ($i = 0; $i < count($t2->params); $i++) {
            self::unify($t1->params[$i], $t2->params[$i]);
          }
        } else {
          throw new types\UnificationFailure();
        }
      } else if ($t1 instanceof types\Tuple) {
        if ($t2 instanceof types\Tuple && count($t1) === count($t2)) {
          for ($i = 0; $i < count($t1); $i++) {
            self::unify($t1->members[$i], $t2->members[$i]);
          }
        } else {
          throw new types\UnificationFailure();
        }
      } else if ($t1 instanceof types\Func) {
        if ($t2 instanceof types\Func) {
          self::unify($t1->input, $t2->input);
          self::unify($t1->output, $t2->output);
        } else {
          throw new types\UnificationFailure();
        }
      } else if ($t1 instanceof types\FixedTypeVar) {
        if (($t2 instanceof types\FixedTypeVar) === false || $t1->get_id() !== $t2->get_id()) {
          throw new types\UnificationFailure();
        }
      } else if ($t1 instanceof types\Atomic) {
        if (($t2 instanceof types\Atomic) === false || $t1->name !== $t2->name) {
          throw new types\UnificationFailure();
        }
      } else {
        die("unknown type: " . get_class($t1) . PHP_EOL);
      }
    }
  }

  /**
   * @param ast\Note $note
   * @param bool     $is_free
   * @return types\Type
   * @throws Error
   */
  private static function note_to_type(ast\Note $note, bool $is_free): types\Type {
    if ($note instanceof ast\UnitNote) {
      return types\Atomic::unit();
    }

    if ($note instanceof ast\GroupedNote) {
      return self::note_to_type($note->inner, $is_free);
    }

    if ($note instanceof ast\NamedNote) {
      if ($type = $note->path->tail->get('symbol')->get(self::TYPE_KEY)) {
        assert($type instanceof types\Type);
        $type = self::fresh($type);
        return $type;
      } else {
        die("unknown type: " . $note->path->tail . PHP_EOL);
      }
    }

    if ($note instanceof ast\TypeParamNote) {
      if ($type = $note->get('symbol')->get($is_free ? 'free' : 'fixed')) {
        return $type;
      }

      $type = $is_free
        ? new types\FreeTypeVar($note->name, null)
        : new types\FixedTypeVar($note->name);

      $note->get('symbol')->set($is_free ? 'free' : 'fixed', $type);
      return $type;
    }

    if ($note instanceof ast\TupleNote) {
      $members = [];
      foreach ($note->members as $member) {
        $members[] = self::note_to_type($member, $is_free);
      }
      return new types\Tuple($members);
    }

    if ($note instanceof ast\ListNote) {
      $elements = self::note_to_type($note->elements, $is_free);
      return new types\ListType($elements);
    }

    if ($note instanceof ast\FuncNote) {
      $input  = self::note_to_type($note->input, $is_free);
      $output = self::note_to_type($note->output, $is_free);
      return new types\Func($input, $output);
    }

    if ($note instanceof ast\ParameterizedNote) {
      $inner = self::note_to_type($note->inner, $is_free);
      $inner = self::fresh($inner);

      if ($inner instanceof types\Enum) {
        if (empty($inner->params)) {
          $note_span = $note->get('span');
          throw Errors::type_does_not_allow_params($note_span, $inner);
        } else if (count($inner->params) !== count($note->params)) {
          $note_span = $note->get('span');
          $expected  = count($inner->params);
          $found     = count($note->params);
          throw Errors::wrong_number_of_type_params($note_span, $inner, $expected, $found);
        }

        foreach ($note->params as $index => $param) {
          $inner_param_type = $inner->params[$index];
          $new_instance     = self::note_to_type($param, $is_free);
          $inner_param_type->set_instance($new_instance);
        }

        return $inner;
      } else {
        $note_span = $note->get('span');
        throw Errors::type_does_not_allow_params($note_span, $inner);
      }
    }

    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  /**
   * @param ast\Pattern $pat
   * @return types\Type
   * @throws types\UnificationFailure
   */
  private static function pattern_to_type(ast\Pattern $pat): types\Type {
    if ($pat instanceof ast\ConstPattern) {
      switch (true) {
        case $pat->literal->value instanceof val\StringValue:
          return types\Atomic::str();
        case $pat->literal->value instanceof val\FloatValue:
          return types\Atomic::float();
        case $pat->literal->value instanceof val\IntegerValue:
          return types\Atomic::int();
        case $pat->literal->value instanceof val\BooleanValue:
          return types\Atomic::bool();
        case $pat->literal->value instanceof val\UnitValue:
          return types\Atomic::unit();
        default:
          die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
      }
    }

    if ($pat instanceof ast\WildcardPattern) {
      return new types\FreeTypeVar('_', null);
    }

    if ($pat instanceof ast\ListPattern) {
      $unified_type = new types\FreeTypeVar('_', null);
      foreach ($pat->elements as $elem_pat) {
        $elem_type = self::pattern_to_type($elem_pat);
        self::unify($unified_type, $elem_type);
      }

      if ($pat->glob) {
        $glob_type = self::pattern_to_type($pat->glob->binding);
        self::unify(new types\ListType($unified_type), $glob_type);
      }

      return new types\ListType($unified_type);
    }

    if ($pat instanceof ast\VariablePattern) {
      $type = new types\FreeTypeVar('_', null);
      $pat->name->get('symbol')->set(self::TYPE_KEY, $type);
      return $type;
    }

    if ($pat instanceof ast\FormPattern) {
      $enum_type = end($pat->path->head)->get('symbol')->get(self::TYPE_KEY);
      $enum_type = self::fresh($enum_type);
      assert($enum_type instanceof types\Enum);

      $form_name = $pat->path->tail->value;
      $form_type = $enum_type->forms[$form_name];

      if ($pat instanceof ast\NamedFormPattern) {
        assert($form_type instanceof types\Record);
        foreach ($form_type->fields as $field_name => $field_type) {
          $field_pattern = $pat->pairs[$field_name]->pattern;
          $member_type   = self::pattern_to_type($field_pattern);
          self::unify($field_type, $member_type);
        }
      } else if ($pat instanceof ast\OrderedFormPattern) {
        assert($form_type instanceof types\Tuple);
        foreach ($form_type->members as $index => $member_type) {
          $member_pattern = $pat->order[$index];
          $pattern_type   = self::pattern_to_type($member_pattern);
          self::unify($member_type, $pattern_type);
        }
      }

      return $enum_type;
    }

    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  private static function block_ret_span(ast\BlockNode $block): Spanlike {
    if (empty($block->stmts)) {
      return $block->get('span');
    }

    $last_stmt      = end($block->stmts);
    $last_stmt_span = $last_stmt->get('span');
    assert($last_stmt_span instanceof Spanlike);

    if ($last_stmt instanceof ast\SemiStmt) {
      return $last_stmt_span->to()->prev_column();
    } else {
      return $last_stmt_span;
    }
  }
}
