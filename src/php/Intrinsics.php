<?php

namespace Cthulhu\php;

use Cthulhu\lib\panic\Panic;
use Cthulhu\php\names\Symbol;
use Cthulhu\val\IntegerValue;
use Cthulhu\val\StringValue;

class Intrinsics {
  /**
   * @param string       $name
   * @param nodes\Expr[] $args
   * @return nodes\Expr
   * @noinspection PhpInconsistentReturnPointsInspection
   */
  public static function build_intrinsic_expr(string $name, array $args): nodes\Expr {
    switch ($name) {
      case 'square_root':
        return self::sqrt(...$args);
      case 'floor':
        return self::float_floor(...$args);
      case 'read_argv':
        return self::read_argv();
      case 'prepend':
        return self::prepend(...$args);
      case 'count':
        return self::count(...$args);
      case 'array_key_exists':
        return self::array_key_exists(...$args);
      case 'subscript':
        return self::subscript(...$args);
      case 'mt_rand':
        return self::mt_rand(...$args);
      case 'print':
        return self::php_print(...$args);
      case 'str_concat':
        return self::str_concat(...$args);
      case 'cast_int_to_string':
        return self::cast_int_to_string(...$args);
      case 'cast_float_to_string':
        return self::cast_float_to_string(...$args);
      case 'any_eq':
        return self::any_eq(...$args);
      case 'any_lt':
        return self::any_lt(...$args);
      case 'any_lte':
        return self::any_lte(...$args);
      case 'any_gt':
        return self::any_gt(...$args);
      case 'any_gte':
        return self::any_gte(...$args);
      case 'negate':
        return self::negate(...$args);
      case 'any_pow':
        return self::any_pow(...$args);
      case 'bool_and':
        return self::bool_and(...$args);
      case 'bool_or':
        return self::bool_or(...$args);
      case 'int_add':
        return self::int_add(...$args);
      case 'int_sub':
        return self::int_sub(...$args);
      case 'int_mul':
        return self::int_mul(...$args);
      case 'float_add':
        return self::float_add(...$args);
      case 'float_sub':
        return self::float_sub(...$args);
      case 'float_mul':
        return self::float_mul(...$args);
      case 'float_div':
        return self::float_div(...$args);
      case 'file_does_exist':
        return self::file_does_exist(...$args);
      case 'file_get_contents':
        return self::file_get_contents(...$args);
      case 'file_to_abspath':
        return self::file_to_abspath(...$args);
      default:
        Panic::with_reason(__LINE__, __FILE__, "unknown intrinsic named '$name'");
    }
  }

  private static function sqrt(nodes\Expr $a): nodes\Expr {
    return new nodes\CallExpr(
      new nodes\ReferenceExpr(
        new nodes\Reference(
          'sqrt',
          new Symbol()),
        false),
      [
        $a,
      ]);
  }

  private static function float_floor(nodes\Expr $a): nodes\Expr {
    return new nodes\CastExpr('int', new nodes\CallExpr(
      new nodes\ReferenceExpr(
        new nodes\Reference(
          'floor',
          new Symbol()),
        false),
      [
        $a,
      ]));
  }

  private static function read_argv(): nodes\Expr {
    return new nodes\CallExpr(
      new nodes\ReferenceExpr(
        new nodes\Reference(
          'array_slice',
          new Symbol()),
        false),
      [
        new nodes\SubscriptExpr(
          new nodes\VariableExpr(
            new nodes\Variable('_SERVER', new Symbol())),
          new nodes\StrLiteral(
            StringValue::from_safe_scalar('argv'))),
        new nodes\IntLiteral(
          IntegerValue::from_scalar(1)),
      ]);
  }

  private static function prepend(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\OrderedArrayExpr([ $a, new nodes\Splat($b) ]);
  }

  private static function count(nodes\Expr $a): nodes\Expr {
    return new nodes\CallExpr(
      new nodes\ReferenceExpr(
        new nodes\Reference(
          'count',
          new Symbol()),
        false),
      [ $a ]);
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

  private static function any_eq(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('==', $a, $b);
  }

  private static function any_lt(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('<', $a, $b);
  }

  private static function any_lte(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('<=', $a, $b);
  }

  private static function any_gt(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('>', $a, $b);
  }

  private static function any_gte(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('>=', $a, $b);
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

  private static function bool_and(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('&&', $a, $b);
  }

  private static function bool_or(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('||', $a, $b);
  }

  private static function int_add(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('+', $a, $b);
  }

  private static function int_sub(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('-', $a, $b);
  }

  private static function int_mul(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('*', $a, $b);
  }

  private static function float_add(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('+', $a, $b);
  }

  private static function float_sub(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('-', $a, $b);
  }

  private static function float_mul(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('*', $a, $b);
  }

  private static function float_div(nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr('/', $a, $b);
  }

  private static function file_does_exist(nodes\Expr $a): nodes\Expr {
    return self::binary(
      '&&',
      self::stdlib('file_exists', $a),
      self::unary(
        '!',
        self::stdlib('is_dir', $a)));
  }

  private static function file_get_contents(nodes\Expr $a): nodes\Expr {
    return self::stdlib('file_get_contents', $a);
  }

  private static function file_to_abspath(nodes\Expr $a): nodes\Expr {
    return self::binary('?:', self::stdlib('realpath', $a), $a);
  }

  private static function unary(string $op, nodes\Expr $a): nodes\Expr {
    return new nodes\UnaryExpr($op, $a);
  }

  private static function binary(string $op, nodes\Expr $a, nodes\Expr $b): nodes\Expr {
    return new nodes\BinaryExpr($op, $a, $b);
  }

  private static function stdlib(string $name, ...$args): nodes\Expr {
    return new nodes\CallExpr(
      new nodes\ReferenceExpr(
        new nodes\Reference(
          $name,
          new Symbol()),
        false),
      $args);
  }
}
