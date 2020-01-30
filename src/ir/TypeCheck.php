<?php

namespace Cthulhu\ir;

use Cthulhu\ast\nodes as ast;
use Cthulhu\ir\names\Binding;
use Cthulhu\ir\names\TypeSymbol;
use Cthulhu\ir\types\ParameterContext;
use Cthulhu\lib\trees\Path;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\loc\Span;
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
    /* @var ParameterContext[] $context_stack */
    $context_stack = [];

    /* @var types\Type[] $return_stack */
    $return_stack = [];

    foreach ($bindings as $binding) {
      $binding->symbol->set(self::TYPE_KEY, new types\Atomic($binding->name));
    }

    Visitor::walk($tree, [
      'enter(IntrinsicSignature)' => function (ast\IntrinsicSignature $sig) {
        $free_ctx    = new ParameterContext(null);
        $free_inputs = [];
        if ($sig->params instanceof ast\TupleNote) {
          foreach ($sig->params->members as $note) {
            $free_inputs[] = self::note_to_type($free_ctx, $note, true);
          }
        } else {
          $free_inputs[] = self::note_to_type($free_ctx, $sig->params, true);
        }

        $free_output = self::note_to_type($free_ctx, $sig->returns, true);
        $free_type   = types\Func::from_input_array($free_inputs, $free_output);
        $sig->name->get('symbol')->set(self::TYPE_KEY, $free_type);
      },

      'EnumItem' => function (ast\EnumItem $enum) {
        $free_ctx = new ParameterContext(null);

        $free_params = [];
        foreach ($enum->params as $note) {
          $free_params[] = self::note_to_type($free_ctx, $note, true);
        }

        $free_forms = [];
        foreach ($enum->forms as $form) {
          if ($form instanceof ast\NamedFormDecl) {
            $fields = [];
            foreach ($form->params as $pair) {
              $field_name          = $pair->name->value;
              $field_type          = self::note_to_type($free_ctx, $pair->note, true);
              $fields[$field_name] = $field_type;
            }
            $free_forms[$form->name->value] = new types\Record($fields);
          } else if ($form instanceof ast\OrderedFormDecl) {
            $members = [];
            foreach ($form->params as $note) {
              $member_type = self::note_to_type($free_ctx, $note, true);
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

      'enter(FnItem)' => function (ast\FnItem $item) use (&$context_stack, &$return_stack) {
        $free_ctx  = new ParameterContext(null);
        $fixed_ctx = new ParameterContext(null);
        array_push($context_stack, $fixed_ctx);

        $free_inputs = [];
        foreach ($item->params->params as $param) {
          $free_inputs[] = self::note_to_type($free_ctx, $param->note, true);
          $fixed_type    = self::note_to_type($fixed_ctx, $param->note, false);
          $param->name->get('symbol')->set(self::TYPE_KEY, $fixed_type);
        }

        $free_output  = self::note_to_type($free_ctx, $item->returns, true);
        $fixed_output = self::note_to_type($fixed_ctx, $item->returns, false);
        array_push($return_stack, $fixed_output);

        $free_type = types\Func::from_input_array($free_inputs, $free_output);
        $item->name->get('symbol')->set(self::TYPE_KEY, $free_type);
      },
      'exit(FnItem)' => function (ast\FnItem $item) use (&$context_stack, &$return_stack) {
        array_pop($context_stack);

        $sig_type = array_pop($return_stack);
        $ret_type = $item->body->get(self::TYPE_KEY);

        try {
          self::unify($sig_type, $ret_type);
        } catch (types\UnificationFailure $failure) {
          $ret_span = empty($item->body->stmts)
            ? $item->body->get('span')
            : end($item->body->stmts)->get('span');
          $sig_span = $item->returns->get('span');
          throw Errors::wrong_ret_type($ret_span, $sig_span, $ret_type, $sig_type);
        }
      },

      'exit(LetStmt)' => function (ast\LetStmt $stmt) use (&$context_stack) {
        $expr_type = $stmt->expr->get(self::TYPE_KEY);

        if ($stmt->note) {
          $param_ctx = end($context_stack);
          $note_type = self::note_to_type($param_ctx, $stmt->note, false);

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
        if (!($cond_type instanceof types\Atomic) && $cond_type->name !== 'Bool') {
          // TODO
          die("condition must return the type `Bool`, found the type `$cond_type` instead");
        }

        $consequent_type = $expr->consequent->get(self::TYPE_KEY);
        if ($expr->alternate) {
          $alternate_type = $expr->alternate->get(self::TYPE_KEY);
          try {
            self::unify($consequent_type, $alternate_type);
          } catch (types\UnificationFailure $failure) {
            // TODO
            die("the consequent and alternate branches must return values of the same type. found `$consequent_type` and `$alternate_type` instead\n");
          }
        } else {
          try {
            self::unify($consequent_type, types\Atomic::unit());
          } catch (types\UnificationFailure $failure) {
            // TODO
            die("if no alternate branch is given, the consequent branch must return the type `()`, found `$consequent_type` instead\n");
          }
        }

        $expr->set(self::TYPE_KEY, $consequent_type);
      },

      'enter(MatchArm)' => function (ast\MatchArm $arm, Path $path) use (&$context_stack) {
        $match_expr = $path->parent->node;
        assert($match_expr instanceof ast\MatchExpr);

        $disc_type = $match_expr->discriminant->get(self::TYPE_KEY);
        assert($disc_type instanceof types\Type);

        $param_ctx    = end($context_stack);
        $pattern_type = self::pattern_to_type($param_ctx, $arm->pattern);

        try {
          self::unify($disc_type, $pattern_type);
        } catch (types\UnificationFailure $failure) {
          die("pattern expected an argument of the type $pattern_type, found $disc_type instead\n");
        }
      },

      'exit(MatchExpr)' => function (ast\MatchExpr $expr) {
        $type = $expr->arms[0]->handler->get(self::TYPE_KEY);
        for ($i = 1; $i < count($expr->arms); $i++) {
          self::unify($type, $expr->arms[$i]->handler->get(self::TYPE_KEY));
        }
        $expr->set(self::TYPE_KEY, $type);
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

        $call_type = $expr->operator->get(self::TYPE_KEY);
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
        $call_type  = $expr->operator->get(self::TYPE_KEY);
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
        $elements_type = new types\FreeTypeVar('_', new TypeSymbol());
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
        }
        $t1->set_instance($t2);
      }
    } else if ($t2 instanceof types\FreeTypeVar) {
      self::unify($t2, $t1);
    } else {
      assert($t1 instanceof types\ConcreteType);
      assert($t2 instanceof types\ConcreteType);

      if ($t1 instanceof types\ListType) {
        if ($t2 instanceof types\ListType) {
          self::unify($t1->elements, $t2->elements);
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

  private static function note_to_type(ParameterContext $ctx, ast\Note $note, bool $is_free): types\Type {
    if ($note instanceof ast\UnitNote) {
      return types\Atomic::unit();
    }

    if ($note instanceof ast\GroupedNote) {
      return self::note_to_type($ctx, $note->inner, $is_free);
    }

    if ($note instanceof ast\NamedNote) {
      if ($type = $note->path->tail->get('symbol')->get(self::TYPE_KEY)) {
        return $type;
      } else {
        die("unknown type: " . $note->path->tail . PHP_EOL);
      }
    }

    if ($note instanceof ast\TypeParamNote) {
      if ($type = $ctx->read($note->get('symbol'))) {
        return $type;
      }

      $type = $is_free
        ? new types\FreeTypeVar($note->name, $note->get('symbol'))
        : new types\FixedTypeVar($note->name);

      $ctx->write($note->get('symbol'), $type);
      return $type;
    }

    if ($note instanceof ast\TupleNote) {
      $members = [];
      foreach ($note->members as $member) {
        $members[] = self::note_to_type($ctx, $member, $is_free);
      }
      return new types\Tuple($members);
    }

    if ($note instanceof ast\ListNote) {
      $elements = self::note_to_type($ctx, $note->elements, $is_free);
      return new types\ListType($elements);
    }

    if ($note instanceof ast\FuncNote) {
      $input  = self::note_to_type($ctx, $note->input, $is_free);
      $output = self::note_to_type($ctx, $note->output, $is_free);
      return new types\Func($input, $output);
    }

    echo get_class($note) . PHP_EOL;
    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  /**
   * @param ParameterContext $ctx
   * @param ast\Pattern      $pat
   * @return types\Type
   * @throws types\UnificationFailure
   */
  private static function pattern_to_type(ParameterContext $ctx, ast\Pattern $pat): types\Type {
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
      return new types\FreeTypeVar('_', new TypeSymbol());
    }

    if ($pat instanceof ast\VariablePattern) {
      $type = new types\FreeTypeVar('_', new TypeSymbol());
      $pat->name->get('symbol')->set(self::TYPE_KEY, $type);
      return $type;
    }

    if ($pat instanceof ast\FormPattern) {
      $enum_type = self::fresh(end($pat->path->head)->get('symbol')->get(self::TYPE_KEY));
      assert($enum_type instanceof types\Enum);

      $form_name = $pat->path->tail->value;
      $form_type = $enum_type->forms[$form_name];

      if ($pat instanceof ast\NamedFormPattern) {
        assert($form_type instanceof types\Record);
        foreach ($form_type->fields as $field_name => $field_type) {
          $field_pattern = $pat->pairs[$field_name];
          self::unify($field_type, self::pattern_to_type($ctx, $field_pattern));
        }
      } else if ($pat instanceof ast\OrderedFormPattern) {
        assert($form_type instanceof types\Tuple);
        foreach ($form_type->members as $index => $member_type) {
          $member_pattern = $pat->order[$index];
          self::unify($member_type, self::pattern_to_type($ctx, $member_pattern));
        }
      }

      return $enum_type;
    }

    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }
}
