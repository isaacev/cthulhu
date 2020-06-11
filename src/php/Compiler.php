<?php

namespace Cthulhu\php;

use Cthulhu\ir\arity\Arity;
use Cthulhu\ir\arity\KnownMultiArity;
use Cthulhu\ir\names\VarSymbol;
use Cthulhu\ir\nodes as ir;
use Cthulhu\ir\types\Record;
use Cthulhu\ir\types\Tuple;
use Cthulhu\lib\trees\Path;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\names\Symbol;
use Cthulhu\php\nodes as php;
use Cthulhu\val\BooleanValue;
use Cthulhu\val\IntegerValue;
use Cthulhu\val\StringValue;

class Compiler {
  private Names $names;
  private ExpressionStack $expressions;
  private StatementAccumulator $statements;
  private NamespaceAccumulator $namespaces;
  private PatternAccumulator $patterns;

  private function __construct() {
    $this->names       = new Names();
    $this->expressions = new ExpressionStack();
    $this->statements  = new StatementAccumulator($this->expressions);
    $this->namespaces  = new NamespaceAccumulator($this->names, $this->statements);
    $this->patterns    = new PatternAccumulator();
  }

  public static function root(ir\Root $root): php\Program {
    $ctx = new self();

//    $stdout = new \Cthulhu\lib\fmt\StreamFormatter(STDOUT);
//    $root->build()->write($stdout)
//      ->newline()
//      ->newline();

    Visitor::walk($root, [
      'enter(Module)' => function (ir\Module $mod) use ($ctx) {
        if ($mod->name) {
          $mod_ref = $mod->name->symbol->get('php/ref');
          $ctx->namespaces->open($mod_ref);
        } else {
          $ctx->namespaces->open_anonymous();
        }
      },
      'exit(Module)' => function (ir\Module $mod) use ($ctx) {
        if ($mod->name) {
          $ctx->namespaces->close();
        } else {
          $ctx->namespaces->close_anonymous();
        }
      },

      'Enum' => function (ir\Enum $enum) use ($ctx) {
        $base_name = $enum->name->get('php/name');
        $base_ref  = $enum->name->symbol->get('php/ref');
        $base_stmt = new php\ClassStmt(true, $base_name, null, [], null);
        $ctx->statements->push_stmt($base_stmt);

        foreach ($enum->forms as $form) {
          $ctx->names->enter_func_scope();
          $ctx->statements->push_block();
          $form_body   = [];
          $ctor_params = [];

          if ($form instanceof ir\NamedForm) {
            $args_var = $ctor_params[] = $ctx->names->tmp_var();
            foreach ($form->mapping as $field_name) {
              $field_var   = $ctx->names->name_to_var($field_name);
              $form_body[] = new php\PropertyNode(true, $field_var);
              $bind_stmt   = new php\AssignStmt(
                new php\PropertyAccessExpr(
                  new php\ThisExpr(),
                  $field_var),
                new php\SubscriptExpr(
                  new php\VariableExpr($args_var),
                  new php\StrLiteral(
                    StringValue::from_safe_scalar($field_var->value))),
                null);
              $ctx->statements->push_stmt($bind_stmt);
            }
          } else if ($form instanceof ir\OrderedForm) {
            for ($i = 0; $i < count($form->order); $i++) {
              $order_var = $ctor_params[] = $ctx->names->tmp_var();
              $bind_stmt = new php\AssignStmt(
                new php\DynamicPropertyAccessExpr(
                  new php\ThisExpr(),
                  new php\IntLiteral(
                    IntegerValue::from_scalar($i))),
                new php\VariableExpr($order_var),
                null);
              $ctx->statements->push_stmt($bind_stmt);
            }
          }

          $ctor_body = $ctx->statements->pop_block();
          $ctx->names->exit_func_scope();
          $form_body[] = new php\MagicMethodNode('__construct', $ctor_params, $ctor_body);

          $form_name = $form->name->get('php/name');
          $form_stmt = new php\ClassStmt(false, $form_name, $base_ref, $form_body, null);
          $ctx->statements->push_stmt($form_stmt);
        }
      },

      'enter(Def)' => function (ir\Def $def) use ($ctx) {
        $ctx->names->enter_func_scope();

        // Assign PHP variable names for the function and for its parameters
        $ctx->names->name_to_ref_name($def->name, $ctx->namespaces->current_ref());
        foreach ($def->params->names as $param) {
          $ctx->names->name_to_var($param);
        }

        // Create a block to collect statements inside of the function body
        $ctx->statements->push_block();
        $ctx->statements->push_yield_strategy(YieldStrategy::SHOULD_RETURN);
      },
      'exit(Def)' => function (ir\Def $def) use ($ctx) {
        $should_ret  = $ctx->statements->peek_yield_strategy() === YieldStrategy::SHOULD_RETURN;
        $is_ret_stmt = ($def->body && ($def->body->last_stmt() instanceof ir\Ret));
        if ($should_ret && $is_ret_stmt === false) {
          $ctx->statements->push_stmt(new php\ReturnStmt(new php\NullLiteral(), null));
        }

        $ctx->statements->pop_yield_strategy();
        $name = $ctx->names->name_to_ref_name($def->name, $ctx->namespaces->current_ref());

        $params = [];
        foreach ($def->params->names as $param) {
          $param_var = $param->symbol->get('php/var');
          assert($param_var instanceof php\Variable);
          $params[] = $param_var;
        }

        $head = new php\FuncHead($name, $params);

        $stmt  = new php\FuncStmt($head, $ctx->statements->pop_block(), [], null);
        $scope = $ctx->names->exit_func_scope();
        $stmt->set('scope', $scope);
        $ctx->statements->push_stmt($stmt);
      },
      'exit(Let)' => function (ir\Let $let) use ($ctx) {
        if ($let->expr instanceof ir\BranchExpr) {
          // If the let statement is bound to a branch expression, then the
          // branches have already bound values to the variable so pop the null
          // literal from the stack and create no additional statements.
          assert($ctx->expressions->pop() instanceof php\NullLiteral);
          return;
        }

        $expr = $ctx->expressions->pop();
        $name = $ctx->names->name_to_var($let->name);
        $stmt = new php\AssignStmt($name, $expr, null);
        $ctx->statements->push_stmt($stmt);
      },
      'exit(Pop)' => function (ir\Pop $pop) use ($ctx) {
        if ($pop->expr instanceof ir\BranchExpr) {
          // If the pop statement wraps a branch expression, no additional
          // statements are required. Just pop the null literal from the stack.
          assert($ctx->expressions->pop() instanceof php\NullLiteral);
          return;
        }

        $expr = $ctx->expressions->pop();
        $stmt = new php\SemiStmt($expr, null);
        $ctx->statements->push_stmt($stmt);
      },
      'exit(Ret)' => function (ir\Ret $ret) use ($ctx) {
        if ($ret->expr instanceof ir\BranchExpr) {
          // If the ret statement is bound to a branch expression, no additional
          // statements are required. Just pop the null literal from the stack.
          assert($ctx->expressions->pop() instanceof php\NullLiteral);
          return;
        }

        $expr = $ctx->expressions->pop();
        switch ($ctx->statements->peek_yield_strategy()) {
          case YieldStrategy::SHOULD_RETURN:
            $stmt = new php\ReturnStmt($expr, null);
            break;
          case YieldStrategy::SHOULD_ASSIGN:
            $var  = $ctx->statements->peek_return_var();
            $stmt = new php\AssignStmt($var, $expr, null);
            break;
          default:
            $stmt = new php\SemiStmt($expr, null);
        }
        $ctx->statements->push_stmt($stmt);
      },
      'enter(Expr)' => function () use ($ctx) {
        // Record the number of expressions in the expression stack. When the
        // expression exits, that number should be n+1.
        $ctx->expressions->store_stack_depth();
      },
      'exit(Expr)' => function (ir\Expr $expr) use ($ctx) {
        $prior_stack_depth = $ctx->expressions->remember_stack_depth();
        $found_stack_depth = $ctx->expressions->current_stack_depth();
        $expr_name_parts   = explode('\\', get_class($expr));
        $expr_name         = end($expr_name_parts);
        $pushed_exprs      = $found_stack_depth - $prior_stack_depth;
        if ($pushed_exprs > 1) {
          die("$expr_name pushed $pushed_exprs expressions to the stack\n");
        } else if ($pushed_exprs === 0) {
          die("$expr_name pushed no expressions to the stack\n");
        } else if ($pushed_exprs < 0) {
          $abs_pushed_exprs = abs($pushed_exprs);
          die("$expr_name removed $abs_pushed_exprs expressions from the stack\n");
        }
      },

      'enter(BranchExpr)' => function (ir\BranchExpr $expr, Path $path) use ($ctx) {
        $parent = $path->parent->node;
        if ($parent instanceof ir\Ret) {
          $ctx->statements->copy_yield_strategy();
        } else if ($parent instanceof ir\Pop) {
          $ctx->statements->push_yield_strategy(YieldStrategy::SHOULD_IGNORE);
        } else if ($parent instanceof ir\Let) {
          $var = $ctx->names->name_to_var($parent->name);
          $ctx->statements->push_return_var($var);
          $ctx->statements->push_yield_strategy(YieldStrategy::SHOULD_ASSIGN);
        } else {
          $var = $ctx->names->tmp_var();
          $ctx->statements->push_return_var($var);
          $ctx->statements->push_yield_strategy(YieldStrategy::SHOULD_ASSIGN);
        }
      },
      'exit(BranchExpr)' => function (ir\BranchExpr $expr, Path $path) use ($ctx) {
        $parent = $path->parent->node;
        if ($parent instanceof ir\Ret) {
          $ctx->statements->pop_yield_strategy();
        } else if ($parent instanceof ir\Pop) {
          $ctx->statements->pop_yield_strategy();
        } else if ($parent instanceof ir\Let) {
          $ctx->statements->pop_return_var();
          $ctx->statements->pop_yield_strategy();
        } else {
          assert($ctx->expressions->pop() instanceof php\NullLiteral);
          $var = $ctx->statements->pop_return_var();
          $ctx->statements->pop_yield_strategy();
          $ctx->expressions->push(new php\VariableExpr($var));
        }
      },

      'enter(Closure)' => function (ir\Closure $closure) use ($ctx) {
        $ctx->names->enter_closure_scope();

        // Assign PHP variable names for the closure parameters
        foreach ($closure->names->names as $param) {
          $ctx->names->name_to_var($param);
        }

        // Create a block to collect statements inside of the function body
        $ctx->statements->push_block();
        $ctx->statements->push_yield_strategy(YieldStrategy::SHOULD_RETURN);
      },
      'exit(Closure)' => function (ir\Closure $closure) use ($ctx) {
        /* @var php\Variable[] $params */
        $params = [];
        foreach ($closure->names->names as $param) {
          $param_var = $param->symbol->get('php/var');
          assert($param_var instanceof php\Variable);
          $params[] = $param_var;
        }

        /* @var php\Variable[] $used */
        $used = [];
        foreach ($closure->closed->names as $closed) {
          $closed_var = $closed->symbol->get('php/var');
          assert($closed_var instanceof php\Variable);
          $used[] = $closed_var;
        }

        $ctx->statements->pop_yield_strategy();
        $body  = $ctx->statements->pop_block();
        $expr  = new php\FuncExpr($params, $used, $body);
        $scope = $ctx->names->exit_closure_scope();
        $expr->set('scope', $scope);
        $ctx->expressions->push($expr);
      },

      'enter(Match)' => function () use ($ctx) {
        $disc_var = $ctx->names->tmp_var();
        $ctx->patterns->push_pattern_context($disc_var);
        $ctx->patterns->peek_pattern_context()->push_accessor(new php\VariableExpr($disc_var));
      },
      'exit(Match)' => function () use ($ctx) {
        /* @var php\IfStmt[] $if_stmts */
        $if_stmts        = [];
        $chained_if_stmt = $ctx->statements->pop_block()->stmt;
        while ($chained_if_stmt !== null) {
          $if_stmts[]      = $chained_if_stmt->from_successor(null);
          $chained_if_stmt = $chained_if_stmt->next;
        }

        // Bind the discriminant expression to a temporary variable
        $disc_var = $ctx->patterns->peek_pattern_context()->discriminant;
        $ctx->patterns->pop_pattern_context();
        $disc_assignment = new php\AssignStmt($disc_var, $ctx->expressions->pop(), null);
        $ctx->statements->push_stmt($disc_assignment);

        // Combine multiple if statements into a single if-elseif-else statement
        $rest = new php\BlockNode(
          new php\DieStmt(
            StringValue::from_safe_scalar("match expression did not cover all possibilities\\n"),
            null)
        );

        foreach (array_reverse($if_stmts) as $index => $if_stmt) {
          if ($index === 0 && $if_stmt->test instanceof php\BoolLiteral) {
            $rest = $if_stmt->consequent;
          } else {
            $rest = new php\IfStmt($if_stmt->test, $if_stmt->consequent, $rest, null);
          }
        }

        $ctx->statements->push_stmt($rest);
        $ctx->expressions->push(new php\NullLiteral());
      },

      'enter(Arms)' => function () use ($ctx) {
        $ctx->statements->push_block();
      },

      'enter(Arm)' => function () use ($ctx) {
        $ctx->statements->push_block();
      },

      'enter(ListPattern)' => function (ir\ListPattern $pattern) use ($ctx) {
        $oper = $pattern->glob ? '>=' : '==';
        $cond = new php\BinaryExpr(
          $oper,
          new nodes\CallExpr(
            new nodes\ReferenceExpr(
              new nodes\Reference(
                'count',
                new Symbol()),
              false),
            [ $ctx->patterns->peek_pattern_context()->peek_accessor() ]),
          new php\IntLiteral(
            IntegerValue::from_scalar($pattern->cardinality()))
        );
        $ctx->patterns->peek_pattern_context()->push_condition($cond);
      },
      'enter(ListPatternMember)' => function (ir\ListPatternMember $member) use ($ctx) {
        $acc = new php\SubscriptExpr(
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          new nodes\IntLiteral(IntegerValue::from_scalar($member->index))
        );
        $ctx->patterns->peek_pattern_context()->push_accessor($acc);
      },
      'exit(ListPatternMember)' => function () use ($ctx) {
        $ctx->patterns->peek_pattern_context()->pop_accessor();
      },
      'enter(Glob)' => function (ir\Glob $glob) use ($ctx) {
        $acc = new php\CallExpr(
          new nodes\ReferenceExpr(
            new nodes\Reference(
              'array_slice',
              new Symbol()),
            false),
          [
            $ctx->patterns->peek_pattern_context()->peek_accessor(),
            new php\IntLiteral(
              IntegerValue::from_scalar($glob->offset)),
          ]
        );
        $ctx->patterns->peek_pattern_context()->push_accessor($acc);
      },
      'exit(Glob)' => function () use ($ctx) {
        $ctx->patterns->peek_pattern_context()->pop_accessor();
      },

      'enter(FormPattern)' => function (ir\FormPattern $pattern) use ($ctx) {
        $cond = new php\BinaryExpr(
          'instanceof',
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          new php\ReferenceExpr($pattern->ref_symbol->get('php/ref'), false)
        );
        $ctx->patterns->peek_pattern_context()->push_condition($cond);
      },

      'VariablePattern' => function (ir\VariablePattern $pattern) use ($ctx) {
        $stmt = new php\AssignStmt(
          $ctx->names->name_to_var($pattern->name),
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          null
        );
        $ctx->statements->push_stmt($stmt);
      },

      'enter(NamedFormField)' => function (ir\NamedFormField $field) use ($ctx) {
        $acc = new php\PropertyAccessExpr(
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          $ctx->names->name_to_var($field->name)
        );
        $ctx->patterns->peek_pattern_context()->push_accessor($acc);
      },
      'exit(NamedFormField)' => function () use ($ctx) {
        $ctx->patterns->peek_pattern_context()->pop_accessor();
      },

      'enter(OrderedFormMember)' => function (ir\OrderedFormMember $member) use ($ctx) {
        $acc = new php\DynamicPropertyAccessExpr(
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          new nodes\IntLiteral(IntegerValue::from_scalar($member->position))
        );
        $ctx->patterns->peek_pattern_context()->push_accessor($acc);
      },
      'exit(OrderedFormMember)' => function () use ($ctx) {
        $ctx->patterns->peek_pattern_context()->pop_accessor();
      },

      'StrConstPattern' => function (ir\StrConstPattern $pattern) use ($ctx) {
        $cond = new php\BinaryExpr(
          '==',
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          new php\StrLiteral($pattern->value));
        $ctx->patterns->peek_pattern_context()->push_condition($cond);
      },
      'FloatConstPattern' => function (ir\FloatConstPattern $pattern) use ($ctx) {
        $cond = new php\BinaryExpr(
          '==',
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          new php\FloatLiteral($pattern->value));
        $ctx->patterns->peek_pattern_context()->push_condition($cond);
      },
      'IntConstPattern' => function (ir\IntConstPattern $pattern) use ($ctx) {
        $cond = new php\BinaryExpr(
          '==',
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          new php\IntLiteral($pattern->value));
        $ctx->patterns->peek_pattern_context()->push_condition($cond);
      },
      'BoolConstPattern' => function (ir\BoolConstPattern $pattern) use ($ctx) {
        $cond = new php\BinaryExpr(
          '==',
          $ctx->patterns->peek_pattern_context()->peek_accessor(),
          new php\BoolLiteral($pattern->value));
        $ctx->patterns->peek_pattern_context()->push_condition($cond);
      },

      'exit(Handler)' => function (ir\Handler $handler) use ($ctx) {
        $conditions = $ctx->patterns->peek_pattern_context()->pop_conditions();
        if (empty($conditions)) {
          $condition = new php\BoolLiteral(BooleanValue::from_scalar(true));
        } else if (count($conditions) === 1) {
          $condition = $conditions[0];
        } else {
          $condition = $conditions[0];
          foreach (array_slice($conditions, 1) as $next_cond) {
            $condition = new nodes\BinaryExpr('&&', $condition, $next_cond);
          }
        }

        $should_ret  = $ctx->statements->peek_yield_strategy() === YieldStrategy::SHOULD_RETURN;
        $is_ret_stmt = ($handler->stmt && ($handler->stmt->last_stmt() instanceof ir\Ret));
        if ($should_ret && $is_ret_stmt === false) {
          $ctx->statements->push_stmt(new php\ReturnStmt(new php\NullLiteral(), null));
        }

        $consequent = $ctx->statements->pop_block();
        $alternate  = null;

        $if_stmt = new php\IfStmt($condition, $consequent, $alternate, null);
        $ctx->statements->push_stmt($if_stmt);
      },

      'enter(Consequent|Alternate)' => function () use ($ctx) {
        $ctx->statements->push_block();
      },
      'exit(Consequent|Alternate)' => function (ir\Stmts $stmts) use ($ctx) {
        $should_ret  = $ctx->statements->peek_yield_strategy() === YieldStrategy::SHOULD_RETURN;
        $is_ret_stmt = ($stmts->first && ($stmts->first->last_stmt() instanceof ir\Ret));
        if ($should_ret && $is_ret_stmt === false) {
          $ctx->statements->push_stmt(new php\ReturnStmt(new php\NullLiteral(), null));
        }

        $block = $ctx->statements->pop_block();
        $ctx->statements->stash_block($block);
      },
      'exit(IfExpr)' => function () use ($ctx) {
        $alternate  = $ctx->statements->unstash_block();
        $consequent = $ctx->statements->unstash_block();
        $condition  = $ctx->expressions->pop();

        $if_stmt = new php\IfStmt($condition, $consequent, $alternate, null);
        $ctx->statements->push_stmt($if_stmt);
        $ctx->expressions->push(new php\NullLiteral());
      },

      //      'enter(Block)' => function () use ($ctx) {
      //        $tmp_var = $ctx->names->tmp_var();
      //        $ctx->statements->push_return_var($tmp_var);
      //      },
      //      'exit(Block)' => function () use ($ctx) {
      //        $tmp_var = $ctx->statements->pop_return_var();
      //        $ctx->expressions->push(new php\VariableExpr($tmp_var));
      //      },

      'exit(Ctor)' => function (ir\Ctor $ctor) use ($ctx) {
        if ($ctor->type instanceof Record) {
          $arg = $ctx->expressions->pop();
          assert($arg instanceof php\AssociativeArrayExpr);
          $args = [ $arg ];
        } else if ($ctor->type instanceof Tuple) {
          $args = $ctx->expressions->pop();
          assert($args instanceof php\OrderedArrayExpr);
          $args = $args->elements;
        } else {
          $arg = $ctx->expressions->pop();
          assert($arg instanceof php\NullLiteral);
          $args = [];
        }

        $ref  = $ctx->names->name_to_ref($ctor->name);
        $expr = new php\NewExpr(
          new php\ReferenceExpr($ref, false),
          $args);
        $ctx->expressions->push($expr);
      },

      'exit(Apply)' => function (ir\Apply $app) use ($ctx) {
        /**
         * A function call can be compiled in a few different ways:
         *
         * 1. A native function call. This solution is clean and efficient but
         *    can only be used when the compiler is certain that such a call will
         *    produce correct PHP. This solution can only be used when the callee
         *    has a known arity.
         *
         * 2. An inline closure that wraps available parameters and exposes a
         *    function interface for providing the rest of the parameters. This
         *    solution can only be used when the callee has a known arity.
         *
         * 3. An inline call to the `curry` function. This solution incurs a
         *    runtime performance penalty (an extra function call wrapping the
         *    *real* call) but is necessary at any call-sites where the arity of
         *    the callee is not known at compile time.
         */

        $callee_arity = $app->callee->get('arity');
        assert($callee_arity instanceof Arity);

        $total_args = count($app->args);
        $args       = $ctx->expressions->pop_multiple($total_args);
        $callee     = $ctx->expressions->pop();
        if ($callee_arity instanceof KnownMultiArity) {
          $ctx->expressions->push(self::over_app($ctx, $callee, $args, $callee_arity));
        } else {
          $ctx->expressions->push(self::curry_app($ctx, $callee, $args));
        }
      },
      'exit(Intrinsic)' => function (ir\Intrinsic $intrinsic) use ($ctx) {
        $args = $ctx->expressions->pop_multiple(count($intrinsic->args));
        $name = $intrinsic->ident;
        $ctx->expressions->push(Intrinsics::build_intrinsic_expr($name, $args));
      },
      'exit(ListExpr)' => function (ir\ListExpr $expr) use ($ctx) {
        $elements = $ctx->expressions->pop_multiple(count($expr->elements));
        $ctx->expressions->push(new php\OrderedArrayExpr($elements));
      },

      'exit(Tuple)' => function (ir\Tuple $tuple) use ($ctx) {
        $exprs = $ctx->expressions->pop_multiple(count($tuple->fields));
        $expr  = new php\OrderedArrayExpr($exprs);
        $ctx->expressions->push($expr);
      },

      'exit(Field)' => function (ir\Field $field) use ($ctx) {
        $name  = $ctx->names->name_to_name($field->name);
        $expr  = $ctx->expressions->pop();
        $field = new php\FieldNode($name, $expr);
        $ctx->expressions->push($field);
      },

      'exit(Record)' => function (ir\Record $record) use ($ctx) {
        /* @var php\FieldNode[] $fields */
        $fields = $ctx->expressions->pop_multiple(count($record->fields));
        $expr   = new php\AssociativeArrayExpr($fields);
        $ctx->expressions->push($expr);
      },

      'Unreachable' => function () use ($ctx) {
        $exit_msg = new php\StrLiteral(StringValue::from_safe_scalar("unreachable code was reached\\n"));
        $die_call = new php\BuiltinCallExpr('die', [ $exit_msg ]);
        $ctx->expressions->push($die_call);
      },
      'NameExpr' => function (ir\NameExpr $expr, Path $path) use ($ctx) {
        $ir_symbol = $expr->name->symbol;
        if ($ir_symbol instanceof VarSymbol) {
          $php_var  = $ir_symbol->get('php/var');
          $php_expr = new nodes\VariableExpr($php_var);
        } else {
          $php_ref   = $ir_symbol->get('php/ref');
          $is_quoted = !(
            $path->parent &&
            $path->parent->node instanceof ir\Apply &&
            $path->parent->node->callee === $expr
          );
          $php_expr  = new nodes\ReferenceExpr($php_ref, $is_quoted);
        }
        $ctx->expressions->push($php_expr);
      },
      'StrLit' => function (ir\StrLit $lit) use ($ctx) {
        $ctx->expressions->push(new php\StrLiteral($lit->str_value));
      },
      'FloatLit' => function (ir\FloatLit $lit) use ($ctx) {
        $ctx->expressions->push(new php\FloatLiteral($lit->float_value));
      },
      'IntLit' => function (ir\IntLit $lit) use ($ctx) {
        $ctx->expressions->push(new php\IntLiteral($lit->int_value));
      },
      'BoolLit' => function (ir\BoolLit $lit) use ($ctx) {
        $ctx->expressions->push(new php\BoolLiteral($lit->bool_value));
      },
      'UnitLit' => function () use ($ctx) {
        $ctx->expressions->push(new php\NullLiteral());
      },
    ]);

    return new php\Program($ctx->namespaces->collect());
  }

  /**
   * @param Compiler   $ctx
   * @param php\Expr   $callee
   * @param php\Expr[] $args
   * @param Arity      $arity
   * @return php\Expr
   */
  private static function over_app(self $ctx, php\Expr $callee, array $args, Arity $arity): php\Expr {
    if (($arity instanceof KnownMultiArity) === false) {
      return self::curry_app($ctx, $callee, $args);
    }

    while (count($args) > 0 && count($args) >= $arity->params) {
      if (($arity instanceof KnownMultiArity) === false) {
        return self::curry_app($ctx, $callee, $args);
      } else if ($arity->params === 0) {
        return $callee;
      }

      $taken_args = array_splice($args, 0, $arity->params);
      $arity      = $arity->apply(count($taken_args));
      $callee     = self::full_app($callee, $taken_args);
    }

    if (!empty($args) && $arity instanceof KnownMultiArity) {
      if (($callee instanceof php\ReferenceExpr) === false) {
        /**
         * If the callee is more complex than a reference expression then it
         * could have side-effects. If the callee has side effects and it's
         * wrapped in an under-application closure, when those side effects
         * occur will change which could change the behavior of the program.
         *
         * The solution is to recognize this case and bind the result of the
         * callee to a temporary variable and use the temporary variable as the
         * callee inside of the under-application closure.
         */
        $tmp_var  = $ctx->names->tmp_var();
        $tmp_stmt = new php\AssignStmt($tmp_var, $callee, null);
        $ctx->statements->push_stmt($tmp_stmt);
        $callee = new php\VariableExpr($tmp_var);
      }

      $callee = self::under_app($ctx, $callee, $args, $arity);
    }

    return $callee;
  }

  /**
   * @param Compiler   $ctx
   * @param php\Expr   $callee
   * @param php\Expr[] $args
   * @return php\Expr
   */
  private static function curry_app(self $ctx, php\Expr $callee, array $args): php\Expr {
    $curry_ref    = $ctx->namespaces->helper('curry');
    $curry_callee = new nodes\ReferenceExpr($curry_ref, false);
    $curry_args   = [ $callee, new nodes\OrderedArrayExpr($args) ];
    return new nodes\CallExpr($curry_callee, $curry_args);
  }

  /**
   * @param Compiler        $ctx
   * @param php\Expr        $callee
   * @param php\Expr[]      $args
   * @param KnownMultiArity $arity
   * @return php\Expr
   */
  private static function under_app(self $ctx, php\Expr $callee, array $args, KnownMultiArity $arity): php\Expr {
    $ctx->names->enter_closure_scope();

    /* @var php\FuncParam[] $closure_params */
    $closure_params = [];
    $leftover_args  = $arity->params - count($args);
    for ($i = 0; $i < $leftover_args; $i++) {
      $var              = $ctx->names->tmp_var();
      $closure_params[] = nodes\FuncParam::from_var($var);
      $args[]           = new nodes\VariableExpr($var);
    }

    $closure_body = new nodes\CallExpr($callee, $args);
    $ctx->names->exit_closure_scope();
    return new nodes\ArrowExpr($closure_params, $closure_body);
  }

  /**
   * @param php\Expr   $callee
   * @param php\Expr[] $args
   * @return php\Expr
   */
  private static function full_app(php\Expr $callee, array $args): php\Expr {
    return new php\CallExpr($callee, $args);
  }
}
