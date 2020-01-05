<?php

namespace Cthulhu\ir;

class Arity {
  public static function analyze(nodes\Program $prog): void {
    Visitor::walk($prog, [
      'exit(NativeFuncItem)' => function (nodes\NativeFuncItem $item) {
        self::exit_native_func_item($item);
      },
      'exit(FuncHead)' => function (nodes\FuncHead $head) {
        self::exit_func_head($head);
      },
      'exit(FuncItem)' => function (nodes\FuncItem $item) {
        self::exit_func_item($item);
      },
      'exit(LetStmt)' => function (nodes\LetStmt $stmt) {
        self::exit_let_stmt($stmt);
      },
      'exit(SemiStmt)' => function (nodes\SemiStmt $stmt) {
        self::exit_semi_stmt($stmt);
      },
      'exit(ReturnStmt)' => function (nodes\ReturnStmt $stmt) {
        self::exit_return_stmt($stmt);
      },
      'exit(Block)' => function (nodes\Block $block) {
        self::exit_block($block);
      },
      'exit(VariablePattern)' => function (nodes\VariablePattern $pattern) {
        self::exit_variable_pattern($pattern);
      },
      'exit(MatchExpr)' => function (nodes\MatchExpr $expr) {
        self::exit_match_expr($expr);
      },
      'exit(IfExpr)' => function (nodes\IfExpr $expr) {
        self::exit_if_expr($expr);
      },
      'exit(CallExpr)' => function (nodes\CallExpr $expr) {
        self::exit_call_expr($expr);
      },
      'exit(BinaryExpr|UnaryExpr|ListExpr|VariantConstructorExpr|Literal)' => function (nodes\Expr $expr) {
        self::exit_nullary_expr($expr);
      },
      'exit(RefExpr)' => function (nodes\RefExpr $expr) {
        self::exit_ref_expr($expr);
      },
    ]);
  }

  public static function validate(nodes\Program $prog): void {
    Visitor::walk($prog, [
      'Stmt' => function (nodes\Stmt $expr) {
        if ($expr->has('arity') === false) {
          die('missing arity for statement');
        }
      },
      'Expr' => function (nodes\Expr $expr) {
        if ($expr->has('arity') === false) {
          die('missing arity for expression');
        }
      },
    ]);
  }

  private static function exit_native_func_item(nodes\NativeFuncItem $item): void {
    $arity = new arity\StaticArity(count($item->note->inputs), new arity\ZeroArity());
    $item->name->get('symbol')->set('arity', $arity);
  }

  private static function exit_func_head(nodes\FuncHead $head): void {
    foreach ($head->params as $param) {
      /**
       * NOTE:
       * Technically, if a parameter has some non-function, non-parametric type
       * like Bool, then the parameter's arity is known at compile time but for
       * now this specificity isn't crucial for the call-site optimizations to
       * work correctly.
       */
      $param->name->get('symbol')->set('arity', new arity\UnknownArity());
    }
  }

  private static function exit_func_item(nodes\FuncItem $item): void {
    $return_arity = $item->body->get('arity');
    $total_params = max(1, count($item->head->params));
    $arity        = new arity\StaticArity($total_params, $return_arity);
    $item->head->name->get('symbol')->set('arity', $arity);
  }

  private static function exit_let_stmt(nodes\LetStmt $stmt): void {
    $arity = $stmt->expr->get('arity');
    assert($arity instanceof arity\Arity);
    $stmt->name->get('symbol')->set('arity', $arity);
    $stmt->set('arity', new arity\ZeroArity());
  }

  private static function exit_semi_stmt(nodes\SemiStmt $stmt): void {
    $stmt->set('arity', new arity\ZeroArity());
  }

  private static function exit_return_stmt(nodes\ReturnStmt $stmt): void {
    $arity = $stmt->expr->get('arity');
    $stmt->set('arity', $arity);
  }

  private static function exit_block(nodes\Block $block): void {
    $arity = end($block->stmts)->get('arity');
    assert($arity instanceof arity\Arity);
    $block->set('arity', $arity);
  }

  private static function exit_variable_pattern(nodes\VariablePattern $pattern): void {
    /**
     * NOTE:
     * Technically, if a parameter has some non-function, non-parametric type
     * like Bool, then the parameter's arity is known at compile time but for
     * now this specificity isn't crucial for the call-site optimizations to
     * work correctly.
     */
    $pattern->name->get('symbol')->set('arity', new arity\UnknownArity());
  }

  private static function exit_match_expr(nodes\MatchExpr $expr): void {
    /* @var arity\Arity|null $arity */
    $arity = null;
    foreach ($expr->arms as $arm) {
      $arm_arity = $arm->handler->stmt->expr->get('arity');
      assert($arm_arity instanceof arity\Arity);
      if ($arity === null) {
        $arity = $arm_arity;
      } else if ($arity->equals($arm_arity) === false) {
        $arity = new arity\UnknownArity();
      }
    }
    $expr->set('arity', $arity ?? new arity\ZeroArity());
  }

  private static function exit_if_expr(nodes\IfExpr $expr): void {
    if ($expr->if_false !== null) {
      $true_arity  = $expr->if_true->get('arity');
      $false_arity = $expr->if_false->get('arity');
      if ($true_arity->equals($false_arity)) {
        $expr->set('arity', $true_arity);
      } else {
        $expr->set('arity', new arity\UnknownArity());
      }
    } else {
      $expr->set('arity', new arity\ZeroArity());
    }
  }

  private static function exit_call_expr(nodes\CallExpr $expr): void {
    /* @var arity\Arity $callee_arity */
    $callee_arity    = $expr->callee->get('arity');
    $total_arguments = count($expr->args);
    $return_arity    = $callee_arity->apply_arguments($total_arguments);
    $expr->set('arity', $return_arity);
  }

  private static function exit_nullary_expr(nodes\Expr $expr): void {
    $expr->set('arity', new arity\ZeroArity());
  }

  private static function exit_ref_expr(nodes\RefExpr $expr): void {
    $arity = $expr->ref->tail_segment->get('symbol')->get('arity');
    assert($arity instanceof arity\Arity);
    $expr->set('arity', $arity);
  }
}
