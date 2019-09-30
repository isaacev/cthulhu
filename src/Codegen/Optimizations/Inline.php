<?php

namespace Cthulhu\Codegen\Optimizations;

use Cthulhu\Codegen\{ Path, PHP, Visitor };

class Inline {
  /**
   * # Function inline optimization
   *
   * ## Current caveats:
   * - If the function returns a non-Void value, the inline optimization will
   *   only be applied if the function body contains a single ReturnStmt.
   */
  public static function apply(PHP\Program $prog): PHP\Program {
    $inline_func_syms = [];
    $inline_func_defs = [];

    /**
     * Pass 1:
     * Determine which function definitions are marked with the `inline`
     * attribute and record the function's symbol and the function's body.
     */
    Visitor::walk($prog, [
      'FuncStmt' => function (Path $path) use (&$inline_func_syms, &$inline_func_defs) {
        $should_inline = array_key_exists('inline', $path->node->attrs)
          ? $path->node->attrs['inline'] === true
          : false;

        if ($should_inline) {
          $inline_func_syms[] = $path->node->name->symbol->id;
          $inline_func_defs[$path->node->name->symbol->id] = $path->node;
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
    return Visitor::edit($prog, [
      'CallExpr' => function (Path $path) use (&$inline_func_syms, &$inline_func_defs) {
        if (($path->node->callee instanceof PHP\ReferenceExpr) === false) {
          // Function being called is a closure or something
          return;
        }

        $symbol = $path->node->callee->reference->symbol;
        if (in_array($symbol->id, $inline_func_syms) === false) {
          // Function being called isn't an inline candidate
          return;
        }

        $func_def = $inline_func_defs[$symbol->id];
        if ($func_def->body->length() > 1 && !($path->parent->node instanceof PHP\SemiStmt)) {
          // Function body is too complex to be used inside of another expression
          return;
        }

        $param_ids = array_map(function ($p) { return $p->symbol->id; }, $func_def->params);
        $param_id_to_arg_expr = array_combine($param_ids, $path->node->args);
        $rewritten_body = Visitor::replace_references($func_def->body, $param_id_to_arg_expr);

        if ($path->parent->node instanceof PHP\SemiStmt) {
          $path->parent->replace_with_multiple($rewritten_body->stmts);
          return;
        }

        if ($rewritten_body->stmts[0] instanceof PHP\ReturnStmt) {
          $path->replace_with($rewritten_body->stmts[0]->expr);
          return;
        }
      },
    ]);
  }
}

