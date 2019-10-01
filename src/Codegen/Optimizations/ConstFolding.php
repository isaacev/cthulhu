<?php

namespace Cthulhu\Codegen\Optimizations;

use Cthulhu\Codegen\{ Path, PHP, Visitor };

class ConstFolding {
  protected static function is_const_expr(PHP\Expr $expr): bool {
    switch (true) {
      case $expr instanceof PHP\StrExpr:
        return true;
      default:
        return false;
    }
  }

  protected static function static_eval(PHP\BinaryExpr $expr): PHP\Expr {
    $left = $expr->left;
    $right = $expr->right;

    if ($left instanceof PHP\StrExpr && $right instanceof PHP\StrExpr) {
      switch ($expr->operator) {
        case '.': return new PHP\StrExpr($left->value . $right->value);
        default:  return $expr;
      }
    }

    return $expr;
  }

  public static function apply(PHP\Program $prog): PHP\Program {
    return Visitor::edit($prog, [
      'postorder(BinaryExpr)' => function (Path $path) {
        if (self::is_const_expr($path->node->left) && self::is_const_expr($path->node->right)) {
          $path->replace_with(self::static_eval($path->node));
        }
      },
    ]);
  }
}
