<?php

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes\Apply;
use Cthulhu\ir\nodes\Closure;
use Cthulhu\ir\nodes\Def;
use Cthulhu\ir\nodes\Expr;
use Cthulhu\ir\nodes\Exprs;
use Cthulhu\ir\nodes\NameExpr;
use Cthulhu\ir\nodes\Root;
use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;

class Inline implements Pass {
  public static function apply(Root $root): Root {
    /* @var Def[] $inline_candidates */
    $inline_candidates = [];
    $current_def       = null;

    Visitor::walk($root, [
      'enter(Def)' => function (Def $def) use (&$inline_candidates, &$current_def) {
        if (self::is_inline_candidate($def)) {
          $inline_candidates[$def->name->symbol->get_id()] = $def;
        }

        $current_def = $def->name->symbol->get_id();
      },
      'exit(Def)' => function () use (&$current_def) {
        $current_def = null;
      },

      'Closure' => function (Closure $closure) use (&$inline_candidates, &$current_def) {
        if (count($closure->closed) > 0) {
          // If the function body contains a closure and that closure uses
          // variables from its parent lexical scope, don't inline the parent
          // function. By not inlining the parent function, we avoid having to
          // also inline any closed-over variables.
          unset($inline_candidates[$current_def]);
        }
      },

      'Apply' => function (Apply $apply) use (&$inline_candidates, &$current_def) {
        if ($apply->callee instanceof NameExpr) {
          $callee_id = $apply->callee->name->symbol->get_id();

          // If the callee symbol ID is equal to the ID of the current function
          // name ID, this call represents a recursive call site and so cannot
          // be inlined without causing infinite code expansion.
          if ($callee_id === $current_def) {
            unset($inline_candidates[$callee_id]);
          }
        }
      },
    ]);

    // FIXME: prevent inlining of calls with compound expression arguments

    $new_root = Visitor::edit($root, [
      'exit(Apply)' => function (Apply $apply, EditablePath $path) use (&$inline_candidates) {
        if ($apply->callee instanceof NameExpr) {
          $callee_id = $apply->callee->name->symbol->get_id();
          if (array_key_exists($callee_id, $inline_candidates)) {
            $def = $inline_candidates[$callee_id];
            assert($def instanceof Def);
            if (count($def->params) === count($apply->args)) {
              $path->replace_with(self::expand($def, $apply->args));
            }
          }
        }
      },
      'exit(Def)' => function (Def $def) use (&$inline_candidates) {
        $def_id = $def->name->symbol->get_id();
        if (array_key_exists($def_id, $inline_candidates)) {
          $inline_candidates[$def_id] = $def;
        }
      },
    ]);

    assert($new_root instanceof Root);
    return $new_root;
  }

  private static function is_inline_candidate(Def $def): bool {
    if ($def->body === null || count($def->body) !== 1) {
      return false;
    }

    $attrs = $def->get('attrs') ?? [];
    foreach ($attrs as $attr) {
      if ($attr['name'] === 'inline' && in_array('never', $attr['args'])) {
        return false;
      }
    }

    return true;
  }

  private static function expand(Def $def, Exprs $args): Expr {
    assert(count($def->params) === count($args));

    // function parameter symbol ID -> argument expression
    $mapping = [];
    for ($i = 0; $i < count($args); $i++) {
      $param_id           = $def->params->get_name($i)->symbol->get_id();
      $arg                = $args->get_expr($i);
      $mapping[$param_id] = $arg;
    }

    $new_expr = Visitor::edit($def->body->expr, [
      'NameExpr' => function (NameExpr $expr, EditablePath $path) use (&$mapping) {
        $param_id = $expr->name->symbol->get_id();
        if (array_key_exists($param_id, $mapping)) {
          $path->replace_with($mapping[$param_id]);
        }
      },
    ]);

    assert($new_expr instanceof Expr);
    return $new_expr;
  }
}
