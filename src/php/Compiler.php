<?php

namespace Cthulhu\php;

use Cthulhu\ir\arity\Arity;
use Cthulhu\ir\arity\KnownMultiArity;
use Cthulhu\ir\names\VarSymbol;
use Cthulhu\ir\nodes as ir;
use Cthulhu\ir\types\Atomic;
use Cthulhu\lib\trees\Path;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\nodes as php;

class Compiler {
  private Names $names;
  private ExpressionStack $expressions;
  private StatementAccumulator $statements;
  private NamespaceAccumulator $namespaces;

  private function __construct() {
    $this->names       = new Names();
    $this->expressions = new ExpressionStack();
    $this->statements  = new StatementAccumulator($this->expressions);
    $this->namespaces  = new NamespaceAccumulator($this->names, $this->statements);
  }

  public static function root(ir\Root $root): php\Program {
    $ctx = new self();

//    $stdout = new StreamFormatter(STDOUT);
//    $root->build()->write($stdout)
//      ->newline()
//      ->newline();

    Visitor::walk($root, [
      'enter(Module)' => function (ir\Module $mod) use ($ctx) {
        if ($mod->name) {
          $ctx->namespaces->open($mod->name);
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
      'enter(Def)' => function (ir\Def $def) use ($ctx) {
        $ctx->names->enter_func_scope();

        // Assign PHP variable names for the function and for its parameters
        $ctx->names->name_to_name($def->name, $ctx->namespaces->current_ref());
        foreach ($def->params->names as $param) {
          $ctx->names->name_to_var($param);
        }

        // Create a block to collect statements inside of the function body
        $ctx->statements->push_return_var($ctx->names->tmp_var());
        $ctx->statements->push_block();
      },
      'exit(Def)' => function (ir\Def $def) use ($ctx) {
        $name = $ctx->names->name_to_name($def->name, $ctx->namespaces->current_ref());

        $params = [];
        foreach ($def->params->names as $param) {
          $param_var = $param->symbol->get('php/var');
          assert($param_var instanceof php\Variable);
          $params[] = $param_var;
        }

        $head = new php\FuncHead($name, $params);

        // If the function returns a value, append a return statement to the
        // end of the function body. The return expression will be a variable
        // that references the result of the last expression in the function.
        $return_type = $def->type->advance(max(1, count($def->params)));
        $return_var  = $ctx->statements->pop_return_var();
        if (Atomic::is_unit($return_type->flatten()) === false) {
          $ret_val = new php\VariableExpr($return_var);
          $ctx->statements->push_stmt(new php\ReturnStmt($ret_val));
        }

        $stmt = new php\FuncStmt($head, $ctx->statements->pop_block(), []);
        $ctx->names->exit_func_scope();
        $ctx->statements->push_stmt($stmt);
      },
      'exit(Let)' => function (ir\Let $let) use ($ctx) {
        $expr = $ctx->expressions->pop();
        if ($let->name) {
          $name = $ctx->names->name_to_var($let->name);
          $stmt = new php\AssignStmt($name, $expr);
        } else {
          $stmt = new php\SemiStmt($expr);
        }
        $ctx->statements->push_stmt($stmt);
      },
      'exit(Ret)' => function (ir\Ret $ret) use ($ctx) {
        assert($ret->next === null);
        $ret_var = $ctx->statements->peek_return_var();
        $expr    = $ctx->expressions->pop();
        $ctx->statements->push_stmt(new php\AssignStmt($ret_var, $expr));
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
      'enter(Stmts)' => function () use ($ctx) {
        $ctx->statements->push_block();
      },
      'exit(Stmts)' => function () use ($ctx) {
        $block = $ctx->statements->pop_block();
        $ctx->statements->stash_block($block);
      },
      'enter(IfExpr)' => function () use ($ctx) {
        $tmp_var = $ctx->names->tmp_var();
        $ctx->statements->push_return_var($tmp_var);
      },
      'exit(IfExpr)' => function () use ($ctx) {
        $alternate  = $ctx->statements->unstash_block();
        $consequent = $ctx->statements->unstash_block();
        $condition  = $ctx->expressions->pop();
        $ret_var    = $ctx->statements->pop_return_var();

        $if_stmt = new php\IfStmt($condition, $consequent, $alternate);
        $ctx->statements->push_stmt($if_stmt);
        $ctx->expressions->push(new php\VariableExpr($ret_var));
      },
      'exit(Apply)' => function (ir\Apply $app) use ($ctx) {
        /**
         * A function call can be compiled in a few different ways:
         *
         * 1. A native function call. This solution is clean and efficient but
         *    can only be used when the compiler is certain that such a call will
         *    produce correct PHP. This solution can only be used when the callee
         *   has a known arity.
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
      'IntLit' => function (ir\IntLit $lit) use ($ctx) {
        $ctx->expressions->push(new php\IntLiteral($lit->int_value));
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
        $tmp_stmt = new php\AssignStmt($tmp_var, $callee);
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
