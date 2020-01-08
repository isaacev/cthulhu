<?php

namespace Cthulhu\php\passes;

use Cthulhu\php;
use Cthulhu\php\visitor;

class Inline {
  /**
   * # Function inline optimization
   *
   * ## Current caveats:
   * - If the function returns a non-unit value, the inline optimization will
   *   only be applied if the function body contains a single ReturnStmt.
   *
   * @param php\nodes\Program $prog
   * @return php\nodes\Program
   */
  public static function apply(php\nodes\Program $prog): php\nodes\Program {
    $inline_func_syms = [];
    $inline_func_defs = [];

    /**
     * Pass 1:
     * Determine which function definitions are marked with the `inline`
     * attribute and record the function's symbol and the function's body.
     */
    visitor\Visitor::walk($prog, [
      'FuncStmt' => function (visitor\Path $path) use (&$inline_func_syms, &$inline_func_defs) {
        assert($path->node instanceof php\nodes\FuncStmt);
        $should_inline = array_key_exists('inline', $path->node->attrs)
          ? $path->node->attrs['inline'] === true
          : false;

        if ($should_inline) {
          $symbol_id                    = $path->node->head->name->symbol->get_id();
          $inline_func_syms[]           = $symbol_id;
          $inline_func_defs[$symbol_id] = $path->node;
        }
      },
    ]);

    /**
     * Pass 2:
     * Find expressions that call the functions identified in Pass 1 as inline
     * candidates. Build a copy of the function body, replacing any references
     * to params with the corresponding expression provided to the function
     * call. Replace the function call with the modified function body.
     */
    $new_prog = visitor\Visitor::edit($prog, [
      'postorder(FuncStmt)' => function (visitor\Path $path) use (&$inline_func_syms, &$inline_func_defs) {
        assert($path->node instanceof php\nodes\FuncStmt);
        $def_id = $path->node->head->name->symbol->get_id();
        if (array_key_exists($def_id, $inline_func_defs)) {
          $inline_func_defs[$def_id] = $path->node;
        }
      },
      'CallExpr' => function (visitor\Path $path) use (&$inline_func_syms, &$inline_func_defs) {
        assert($path->node instanceof php\nodes\CallExpr);
        if (($path->node->callee instanceof php\nodes\ReferenceExpr) === false) {
          // Function being called is a closure or something
          return;
        }

        $call_id = $path->node->callee->reference->symbol->get_id();
        if (in_array($call_id, $inline_func_syms) === false) {
          // Function being called isn't an inline candidate
          return;
        }

        $func_def = $inline_func_defs[$call_id];
        assert($func_def instanceof php\nodes\FuncStmt);

        if ($func_def->body->length() > 1 && $path->parent->node instanceof php\nodes\Expr) {
          // Function body is too complex to be used inside of another expression
          return;
        }

        $param_ids            = array_map(fn($p) => $p->symbol->get_id(), $func_def->head->params);
        $param_id_to_arg_expr = array_combine($param_ids, $path->node->args);
        $rewritten_body       = visitor\Visitor::replace_references($func_def->body, $param_id_to_arg_expr);
        assert($rewritten_body instanceof php\nodes\BlockNode);

        $return_expr    = self::func_body_return_expr($rewritten_body);
        $rewritten_body = self::func_body_without_return_stmt($rewritten_body);

        if ($path->parent->node instanceof php\nodes\Expr && empty($rewritten_body->stmts)) {
          $path->replace_with($return_expr);
        } else if ($path->parent->node instanceof php\nodes\SemiStmt) {
          $path->parent->replace_with_multiple([
            ...$rewritten_body->stmts,
            new php\nodes\SemiStmt($return_expr),
          ]);
        } else if ($path->parent->node instanceof php\nodes\ReturnStmt) {
          $path->parent->replace_with_multiple([
            ...$rewritten_body->stmts,
            new php\nodes\ReturnStmt($return_expr),
          ]);
        } else if ($path->parent->node instanceof php\nodes\AssignStmt) {
          $path->parent->replace_with_multiple([
            ...$rewritten_body->stmts,
            new php\nodes\AssignStmt($path->parent->node->assignee, $return_expr),
          ]);
        }
      },
    ]);

    assert($new_prog instanceof php\nodes\Program);
    return $new_prog;
  }

  private static function func_body_return_expr(php\nodes\BlockNode $block): php\nodes\Expr {
    $last_stmt = end($block->stmts);
    if ($last_stmt instanceof php\nodes\ReturnStmt) {
      return $last_stmt->expr;
    } else {
      return new php\nodes\NullLiteral();
    }
  }

  private static function func_body_without_return_stmt(php\nodes\BlockNode $block): php\nodes\BlockNode {
    $last_stmt = end($block->stmts);
    if ($last_stmt instanceof php\nodes\ReturnStmt) {
      return new php\nodes\BlockNode(array_slice($block->stmts, 0, -1));
    } else {
      return $block;
    }
  }
}

