<?php

namespace Cthulhu\php;

class Intrinsics {
  /**
   * @param string       $name
   * @param nodes\Expr[] $args
   * @return nodes\Expr
   */
  public static function build_intrinsic_expr(string $name, array $args): nodes\Expr {
    switch ($name) {
      case 'php_print':
        return self::php_print(...$args);
      case 'str_concat':
        return self::str_concat(...$args);
      case 'cast_int_to_string':
        return self::cast_int_to_string(...$args);
      case 'any_lt':
        return self::any_lt(...$args);
      case 'any_gt':
        return self::any_gt(...$args);
      case 'negate':
        return self::negate(...$args);
      default:
        die("unknown intrinsic named '$name'\n");
    }
  }

  private static function php_print(nodes\Expr $a): nodes\Expr {
    return new nodes\BuiltinCallExpr('print', [ $a ]);
  }

  private static function str_concat(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('.', $a, $b);
  }

  private static function cast_int_to_string(nodes\Expr $a): nodes\Expr {
    return new nodes\CastExpr('string', $a);
  }

  private static function any_lt(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('<', $a, $b);
  }

  private static function any_gt(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('>', $a, $b);
  }

  private static function negate(nodes\Expr $a): nodes\Expr {
    return new nodes\UnaryExpr('-', $a);
  }
}
