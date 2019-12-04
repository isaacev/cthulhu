<?php

namespace Cthulhu\php\passes;

use Cthulhu\php;
use Cthulhu\php\visitor;

class ConstFolding {
  protected static function is_const_expr(php\nodes\Expr $expr): bool {
    switch (true) {
      case $expr instanceof php\nodes\StrLiteral:
        return true;
      default:
        return false;
    }
  }

  protected static function static_eval(php\nodes\BinaryExpr $expr): php\nodes\Expr {
    $left  = $expr->left;
    $right = $expr->right;

    if ($left instanceof php\nodes\StrLiteral && $right instanceof php\nodes\StrLiteral) {
      switch ($expr->operator) {
        case '.':
          return new php\nodes\StrLiteral($left->value . $right->value);
        default:
          return $expr;
      }
    }

    return $expr;
  }

  public static function apply(php\nodes\Program $prog): php\nodes\Program {
    $new_prog = visitor\Visitor::edit($prog, [
      'postorder(BinaryExpr)' => function (visitor\Path $path) {
        assert($path->node instanceof php\nodes\BinaryExpr);
        if (self::is_const_expr($path->node->left) && self::is_const_expr($path->node->right)) {
          $path->replace_with(self::static_eval($path->node));
        }
      },
    ]);

    assert($new_prog instanceof php\nodes\Program);
    return $new_prog;
  }
}
