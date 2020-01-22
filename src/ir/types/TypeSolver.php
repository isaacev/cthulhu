<?php

namespace Cthulhu\ir\types;

use Cthulhu\ast\nodes\ConstPattern;
use Cthulhu\ast\nodes\FormPattern;
use Cthulhu\ast\nodes\NamedFormPattern;
use Cthulhu\ast\nodes\OrderedFormPattern;
use Cthulhu\ast\nodes\Pattern;
use Cthulhu\ast\nodes\VariablePattern;
use Cthulhu\ast\nodes\WildcardPattern;
use Cthulhu\ir\names\Symbol;
use Cthulhu\val\BooleanValue;
use Cthulhu\val\FloatValue;
use Cthulhu\val\IntegerValue;
use Cthulhu\val\StringValue;
use Cthulhu\val\UnitValue;
use Cthulhu\val\Value;

class TypeSolver {
  /**
   * Evaluates any expression.
   *
   * @param hm\Expr    $expr
   * @param Env        $env
   * @param hm\TypeSet $non_gen
   * @return hm\Type
   */
  public static function expr(hm\Expr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    switch (true) {
      case $expr instanceof hm\LitExpr:
        return self::lit_expr($expr, $env, $non_gen);
      case $expr instanceof hm\VarExpr:
        return self::var_expr($expr, $env, $non_gen);
      case $expr instanceof hm\AppExpr:
        return self::app_expr($expr, $env, $non_gen);
      case $expr instanceof hm\LamExpr:
        return self::lam_expr($expr, $env, $non_gen);
      case $expr instanceof hm\LetExpr:
        return self::let_expr($expr, $env, $non_gen);
      case $expr instanceof hm\DecExpr:
        return self::dec_expr($expr, $env, $non_gen);
      case $expr instanceof hm\DoExpr:
        return self::do_expr($expr, $env, $non_gen);
      case $expr instanceof hm\MatchExpr:
        return self::match_expr($expr, $env, $non_gen);
      case $expr instanceof hm\CtorExpr:
        return self::ctor_expr($expr, $env, $non_gen);
      case $expr instanceof hm\RecordExpr:
        return self::record_expr($expr, $env, $non_gen);
      case $expr instanceof hm\TupleExpr:
        return self::tuple_expr($expr, $env, $non_gen);
      case $expr instanceof hm\UnitExpr:
        return self::unit_expr($expr, $env, $non_gen);
      default:
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  /**
   * @param hm\LitExpr $expr
   * @param Env        $env
   * @param hm\TypeSet $non_gen
   * @return hm\Type
   */
  private static function lit_expr(hm\LitExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    return self::value_to_type($expr->value);
  }

  /**
   * Evaluates the use of a name to reference another name in the environment
   * that was bound by either a let-expr or as a parameter by a parent function
   * expression.
   *
   * @param hm\VarExpr $expr
   * @param Env        $env
   * @param hm\TypeSet $non_gen
   * @return hm\Type
   */
  private static function var_expr(hm\VarExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $type = self::lookup($expr->name, $env, $non_gen);

    if ($expr->has('node')) {
      $expr->get('node')->set('type', $type);
    }

    return $type;
  }

  /**
   * Evaluates a function call (aka function application).
   *
   * @param hm\AppExpr $expr
   * @param Env        $env
   * @param hm\TypeSet $non_gen
   * @return hm\Type
   */
  private static function app_expr(hm\AppExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $func_type = self::expr($expr->func, $env, $non_gen);
    $arg_type  = self::expr($expr->arg, $env, $non_gen);
    $res_type  = new hm\TypeVar();
    $app_type  = new hm\Func($arg_type, $res_type);
    self::unify($app_type, $func_type);

    if ($expr->has('node')) {
      $expr->get('node')->set('type', $res_type);
    }

    return $res_type;
  }

  /**
   * Evaluates the definition of a lambda function.
   *
   * @param hm\LamExpr $expr
   * @param Env        $env
   * @param hm\TypeSet $non_gen
   * @return hm\Func
   */
  private static function lam_expr(hm\LamExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Func {
    $param_type = new hm\TypeVar($expr->param->note);
    $env->write($expr->param->name, $param_type);
    $new_non_gen = new hm\TypeSet($non_gen);
    $new_non_gen->add($param_type);
    $result_type = self::expr($expr->body, $env, $new_non_gen);

    if ($expr->note) {
      self::unify($result_type, $expr->note);
    }

    $type = new hm\Func($param_type, $result_type);

    if ($expr->has('node')) {
      $expr->get('node')->set('type', $type);
    }

    return $type;
  }

  /**
   * Evaluates a let-expression which binds a type to a name in the environment.
   *
   * @param hm\LetExpr $expr
   * @param Env        $env
   * @param hm\TypeSet $non_gen
   * @return hm\Type
   */
  private static function let_expr(hm\LetExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $body_type = self::expr($expr->body, $env, $non_gen);
    $env->write($expr->name, $body_type);
    return $body_type;
  }

  /**
   * Bootstraps a name with an arbitrary type
   *
   * @param hm\DecExpr $expr
   * @param Env        $env
   * @param hm\TypeSet $non_gen
   * @return hm\Type
   */
  private static function dec_expr(hm\DecExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $body_type = $expr->note;
    $env->write($expr->name, $body_type);
    return $body_type;
  }

  /**
   * @param hm\DoExpr  $expr
   * @param Env        $env
   * @param hm\TypeSet $non_gen
   * @return hm\Type
   */
  private static function do_expr(hm\DoExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $result = null;
    foreach ($expr->body as $sub_expr) {
      $result = self::expr($sub_expr, $env, $non_gen);
    }
    if ($result === null) {
      die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    } else {
      return $result;
    }
  }

  /**
   * @param hm\MatchExpr $expr
   * @param Env          $env
   * @param hm\TypeSet   $non_gen
   * @return hm\Type
   */
  private static function match_expr(hm\MatchExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $in_type  = self::expr($expr->discriminant, $env, $non_gen);
    $out_type = new hm\TypeVar();
    foreach ($expr->arms as $arm) {
      /**
       * Step 1: determine the type structure required by the pattern.
       */
      $arm_type = self::bind_pattern($arm->pattern, $env, $non_gen);

      /**
       * Step 2: make sure that the type structure required by the pattern is
       * consistent with the discriminant's type.
       */
      self::unify($in_type, $arm_type);

      /**
       * Step 3: make sure that the return type of handler is consistent with
       * the return type of previous handlers.
       */
      self::unify($out_type, self::expr($arm->handler, $env, $non_gen));
    }

    if ($expr->has('node')) {
      $expr->get('node')->set('type', $out_type);
    }

    return $out_type;
  }

  private static function ctor_expr(hm\CtorExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $enum_type = $expr->enum_symbol->get('type');
    $enum_type = self::fresh($enum_type, $non_gen);
    $form_name = $expr->form_symbol->get('text');
    $form_type = $enum_type->get_form($form_name);
    $args_type = self::expr($expr->args, $env, $non_gen);

    self::unify($form_type, $args_type);

    if ($expr->has('node')) {
      $expr->get('node')->set('type', $enum_type);
    }

    return $enum_type;
  }

  private static function record_expr(hm\RecordExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $fields = [];
    foreach ($expr->fields as $field_name => $field_expr) {
      $fields[$field_name] = self::expr($field_expr, $env, $non_gen);
    }

    $type = new hm\Record($fields);

    if ($expr->has('node')) {
      $expr->get('node')->set('type', $type);
    }

    return $type;
  }

  private static function tuple_expr(hm\TupleExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $fields = [];
    foreach ($expr->fields as $field_expr) {
      $fields[] = self::expr($field_expr, $env, $non_gen);
    }

    $type = new hm\Tuple($fields);

    if ($expr->has('node')) {
      $expr->get('node')->set('type', $type);
    }

    return $type;
  }

  private static function unit_expr(hm\UnitExpr $expr, Env $env, hm\TypeSet $non_gen): hm\Type {
    $type = new hm\Unit();

    if ($expr->has('node')) {
      $expr->get('node')->set('type', $type);
    }

    return $type;
  }

  private static function bind_pattern(Pattern $pattern, Env $env, hm\TypeSet $non_gen): hm\Type {
    if ($pattern instanceof ConstPattern) {
      return self::value_to_type($pattern->literal->value);
    }

    if ($pattern instanceof WildcardPattern) {
      return new hm\TypeVar();
    }

    if ($pattern instanceof VariablePattern) {
      $symbol = $pattern->name->get('symbol');
      $type   = new hm\TypeVar();
      $env->write($symbol, $type);
      return $type;
    }

    if ($pattern instanceof FormPattern) {
      $enum_type = end($pattern->path->head)->get('symbol')->get('type');
      assert($enum_type instanceof hm\Enum);
      $enum_type = self::fresh($enum_type, $non_gen);

      if ($pattern instanceof NamedFormPattern) {
        $form_type = $enum_type->get_form($pattern->path->tail->value);
        assert($form_type instanceof hm\Record);
        foreach ($form_type->fields as $field_name => $sub_type) {
          $sub_pattern = $pattern->pairs[$field_name];
          self::unify($sub_type, self::bind_pattern($sub_pattern, $env, $non_gen));
        }
      } else if ($pattern instanceof OrderedFormPattern) {
        $form_type = $enum_type->get_form($pattern->path->tail->value);
        assert($form_type instanceof hm\Tuple);
        foreach ($form_type->types as $index => $sub_type) {
          $sub_pattern = $pattern->order[$index];
          self::unify($sub_type, self::bind_pattern($sub_pattern, $env, $non_gen));
        }
      } else {
        $form_type = $enum_type->get_form($pattern->path->tail->value);
        assert($form_type instanceof hm\Unit);
      }

      return $enum_type;
    }

    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  private static function value_to_type(Value $value): hm\Type {
    switch (true) {
      case $value instanceof UnitValue:
        return new hm\Unit();
      case $value instanceof BooleanValue:
        return new hm\Nullary('Bool');
      case $value instanceof IntegerValue:
        return new hm\Nullary('Int');
      case $value instanceof FloatValue:
        return new hm\Nullary('Float');
      case $value instanceof StringValue:
        return new hm\Nullary('Str');
      default:
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  public static function unify(hm\Type $t1, hm\Type $t2): void {
    $t1 = self::prune($t1);
    $t2 = self::prune($t2);

    if ($t1 instanceof hm\TypeVar) {
      if ($t1 !== $t2) {
        if (self::occurs_in_type($t1, $t2)) {
          die("recursive unification between $t1 and $t2\n");
        }
        $t1->instance = $t2;
      }
    } else if ($t1 instanceof hm\TypeOper && $t2 instanceof hm\TypeVar) {
      self::unify($t2, $t1);
    } else if ($t1 instanceof hm\TypeOper && $t2 instanceof hm\TypeOper) {
      if ($t1->name !== $t2->name || $t1->arity() !== $t2->arity()) {
        die("type mismatch between $t1 and $t2\n");
      }
      for ($i = 0; $i < $t1->arity(); $i++) {
        self::unify($t1->types[$i], $t2->types[$i]);
      }
    } else {
      die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  private static function prune(hm\Type $t): hm\Type {
    if ($t instanceof hm\TypeVar && $t->instance) {
      $t->instance = self::prune($t->instance);
      return $t->instance;
    } else {
      return $t;
    }
  }

  private static function occurs_in_type(hm\TypeVar $v, hm\Type $t): bool {
    $t = self::prune($t);
    if ($v === $t) {
      return true;
    } else if ($t instanceof hm\TypeOper) {
      return self::occurs_in_types($v, $t->types);
    } else {
      return false;
    }
  }

  /**
   * @param hm\TypeVar $v
   * @param hm\Type[]  $ts
   * @return bool
   */
  private static function occurs_in_types(hm\TypeVar $v, array $ts): bool {
    foreach ($ts as $t) {
      if (self::occurs_in_type($v, $t)) {
        return true;
      }
    }
    return false;
  }

  public static function fresh(hm\Type $t, hm\TypeSet $non_gen): hm\Type {
    $map = new hm\TypeMap();
    $rec = function (hm\Type $t) use (&$rec, &$map, &$non_gen): hm\Type {
      $t = self::prune($t);
      if ($t instanceof hm\TypeVar) {
        if ($non_gen->has($t) === false) {
          if ($map->has($t)) {
            return $map->read($t);
          } else {
            $new_var = new hm\TypeVar();
            $map->write($t, $new_var);
            return $new_var;
          }
        } else {
          return $t;
        }
      } else {
        assert($t instanceof hm\TypeOper);
        return $t->fresh($rec);
      }
    };
    return $rec($t);
  }

  private static function lookup(Symbol $name, Env $env, hm\TypeSet $non_gen): hm\Type {
    return self::fresh($env->read($name), $non_gen);
  }
}
