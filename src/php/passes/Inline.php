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
        if ($func_def->body->length() > 1 && !($path->parent->node instanceof php\nodes\SemiStmt)) {
          // Function body is too complex to be used inside of another expression
          return;
        }

        $param_ids            = array_map(function ($p) {
          return $p->symbol->get_id();
        }, $func_def->head->params);
        $param_id_to_arg_expr = array_combine($param_ids, $path->node->args);
        $rewritten_body       = visitor\Visitor::replace_references($func_def->body, $param_id_to_arg_expr);

        if ($path->parent->node instanceof php\nodes\SemiStmt) {
          $path->parent->replace_with_multiple($rewritten_body->stmts);
          return;
        }

        if (!empty($rewritten_body->stmts) && $rewritten_body->stmts[0] instanceof php\nodes\ReturnStmt) {
          $path->replace_with($rewritten_body->stmts[0]->expr);
          return;
        }
      },
    ]);

    assert($new_prog instanceof php\nodes\Program);
    return $new_prog;
  }
}

