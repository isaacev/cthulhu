<?php

namespace Cthulhu\php;

use Cthulhu\php\names\Symbol;

class Intrinsics {
  /**
   * @param string       $name
   * @param nodes\Expr[] $args
   * @return nodes\Expr
   */
  public static function build_intrinsic_expr(string $name, array $args): nodes\Expr {
    switch ($name) {
      case 'array_key_exists':
        return self::array_key_exists(...$args);
      case 'subscript':
        return self::subscript(...$args);
      case 'mt_rand':
        return self::mt_rand(...$args);
      case 'php_print':
        return self::php_print(...$args);
      case 'str_concat':
        return self::str_concat(...$args);
      case 'cast_int_to_string':
        return self::cast_int_to_string(...$args);
      case 'cast_float_to_string':
        return self::cast_float_to_string(...$args);
      case 'any_lt':
        return self::any_lt(...$args);
      case 'any_gt':
        return self::any_gt(...$args);
      case 'negate':
        return self::negate(...$args);
      case 'any_pow':
        return self::any_pow(...$args);
      case 'int_add':
        return self::int_add(...$args);
      case 'int_mul':
        return self::int_mul(...$args);
      case 'float_add':
        return self::float_add(...$args);
      case 'float_mul':
        return self::float_mul(...$args);
      default:
        die("unknown intrinsic named '$name'\n");
    }
  }

  private static function array_key_exists(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\CallExpr(
      new nodes\ReferenceExpr(
        new nodes\Reference(
          'array_key_exists',
          new Symbol()),
        false),
      [ $a, $b ]);
  }

  private static function subscript(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\SubscriptExpr($a, $b);
  }

  private static function mt_rand(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\CallExpr(
      new nodes\ReferenceExpr(
        new nodes\Reference(
          'mt_rand',
          new Symbol()),
        false),
      [ $a, $b ]);
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

  private static function cast_float_to_string(nodes\Expr $a): nodes\Expr {
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

  private static function any_pow(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\CallExpr(
      new nodes\ReferenceExpr(
        new nodes\Reference(
          'pow',
          new Symbol()),
        false),
      [ $a, $b ]);
  }

  private static function int_add(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('+', $a, $b);
  }

  private static function int_mul(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('*', $a, $b);
  }

  private static function float_add(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('+', $a, $b);
  }

  private static function float_mul(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('*', $a, $b);
  }
}
