<?php

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes2\Apply;
use Cthulhu\ir\nodes2\Expr;
use Cthulhu\ir\nodes2\Exprs;
use Cthulhu\ir\nodes2\Func;
use Cthulhu\ir\nodes2\Let;
use Cthulhu\ir\nodes2\NameExpr;
use Cthulhu\ir\nodes2\Root;
use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;

class Inline implements Pass {
  public static function apply(Root $root): Root {
    $inline_candidates = [];

    Visitor::walk($root, [
      'Let' => function (Let $let) use (&$inline_candidates) {
        if ($let->expr instanceof Func) {
          if ($let->name && self::is_inline_candidate($let->expr)) {
            $inline_candidates[$let->name->symbol->get_id()] = $let->expr;
          }
        }
      },
    ]);

    // FIXME: prevent recursive expansion
    // FIXME: prevent inlining of calls with compound expression arguments

    $new_root = Visitor::edit($root, [
      'exit(Apply)' => function (Apply $apply, EditablePath $path) use (&$inline_candidates) {
        if ($apply->callee instanceof NameExpr) {
          $callee_id = $apply->callee->name->symbol->get_id();
          if (array_key_exists($callee_id, $inline_candidates)) {
            $func = $inline_candidates[$callee_id];
            assert($func instanceof Func);
            if ($func->names === count($apply->args)) {
              $path->replace_with(self::expand($func, $apply->args));
            }
          }
        }
      },

      'exit(Let)' => function (Let $let) use (&$inline_candidates) {
        if ($let->expr instanceof Func) {
          if ($let->name) {
            $let_id = $let->name->symbol->get_id();
            if (array_key_exists($let_id, $inline_candidates)) {
              $inline_candidates[$let->name->symbol->get_id()] = $let->expr;
            }
          }
        }
      },
    ]);

    assert($new_root instanceof Root);
    return $new_root;
  }

  private static function is_inline_candidate(Func $func): bool {
    return count($func->stmt) === 1;
  }

  private static function expand(Func $func, Exprs $args): Expr {
    assert(count($func->names) === count($args));

    // function parameter symbol ID -> argument expression
    $mapping = [];
    for ($i = 0; $i < count($args); $i++) {
      $param_id           = $func->names->get_name($i)->symbol->get_id();
      $arg                = $args->get_expr($i);
      $mapping[$param_id] = $arg;
    }

    $new_expr = Visitor::edit($func->stmt->expr, [
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
