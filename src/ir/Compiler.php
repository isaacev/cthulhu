<?php

namespace Cthulhu\ir;

use Cthulhu\ast\nodes as ast;
use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\names\VarSymbol;
use Cthulhu\ir\nodes2 as ir;
use Cthulhu\ir\types\hm;

class Compiler {
  private types\Env $env;

  private ?ir\Module $module = null;

  /* @var ir\Stmt[] $stmts */
  private array $stmts = [];

  private function __construct(types\Env $env) {
    $this->env = $env;
  }

  private function push_stmt(ir\Stmt $next): void {
    assert(!empty($this->stmts));
    $last = end($this->stmts);
    if ($last === null) {
      array_pop($this->stmts);
      array_push($this->stmts, $next);
    } else {
      $last->mutable_append($next);
    }
  }

  public static function program(ast\Program $prog, types\Env $env): ir\Root {
    $ctx = new self($env);

    foreach ($prog->files as $file) {
      self::file($ctx, $file);
    }

    return new ir\Root($ctx->module);
  }

  private static function file(self $ctx, ast\File $file): void {
    $symbol = $file->name->get('symbol');
    $text   = self::symbol_to_text($symbol);
    $type   = new hm\Nullary('Unit');
    $name   = new ir\Name($type, $text, $symbol);
    $stmts  = self::items($ctx, $file->items);
    $mod    = new ir\Module($name, $stmts, null);

    if ($ctx->module === null) {
      $ctx->module = $mod;
    } else {
      $ctx->module->mutable_append($mod);
    }
  }

  /**
   * @param Compiler   $ctx
   * @param ast\Item[] $items
   * @return ir\Stmt
   */
  private static function items(self $ctx, array $items): ?ir\Stmt {
    array_push($ctx->stmts, null);

    foreach ($items as $item) {
      self::item($ctx, $item);
    }

    return array_pop($ctx->stmts);
  }

  private static function item(self $ctx, ast\Item $item): void {
    switch (true) {
      case $item instanceof ast\IntrinsicItem:
        self::intrinsic_item($ctx, $item);
        break;
      case $item instanceof ast\FnItem:
        self::fn_item($ctx, $item);
        break;
    }
  }

  private static function intrinsic_item(self $ctx, ast\IntrinsicItem $item): void {
    foreach ($item->signatures as $sig) {
      $symbol = $sig->name->get('symbol');
      $text   = self::symbol_to_text($symbol);
      $type   = $ctx->env->read($symbol);
      assert($type instanceof hm\Func);
      $name  = new ir\Name($type, $text, $symbol);
      $ident = $sig->name->value;
      $int   = new ir\Intrinsic($ident, $type);

      $names      = [];
      $exprs      = [];
      $input_type = $type;
      while ($input_type instanceof hm\Func) {
        $name_type   = $input_type->input;
        $name_symbol = new VarSymbol();
        $name_text   = chr(ord('a') + count($names));
        $names[]     = $param_name = new ir\Name($name_type, $name_text, $name_symbol);
        $exprs[]     = new ir\NameExpr($param_name);
        $input_type  = $input_type->output;
      }
      $params = new ir\Names($names);
      $args   = new ir\Exprs($exprs);

      $app  = new ir\Apply($type, $int, $args);
      $func = new ir\Func($type, $params, new ir\Ret($app, null));
      $let  = new ir\Let($name, $func, null);
      $ctx->push_stmt($let);
    }
  }

  private static function fn_item(self $ctx, ast\FnItem $item): void {
    if ($item->name instanceof ast\OperatorRef) {
      $symbol = $item->name->oper->get('symbol');
    } else {
      assert($item->name instanceof ast\LowerName);
      $symbol = $item->name->get('symbol');
    }

    $text = self::symbol_to_text($symbol);
    $type = $ctx->env->read($symbol);
    assert($type instanceof hm\Func);

    $name  = new ir\Name($type, $text, $symbol);
    $names = self::params($ctx, $item->params);
    $stmts = self::stmts($ctx, $item->body->stmts);
    $func  = new ir\Func($type, $names, $stmts);
    $let   = new ir\Let($name, $func, null);

    if (self::is_entry_point($item)) {
      $let->set('entry', true);
    }

    $ctx->push_stmt($let);
  }

  private static function is_entry_point(ast\FnItem $item): bool {
    /* @var ast\Attribute[]|null $attrs */
    $attrs = $item->get('attrs');
    if ($attrs) {
      foreach ($attrs as $attr) {
        if ($attr->name->value === 'entry') {
          return true;
        }
      }
    }
    return false;
  }

  /**
   * @param Compiler        $ctx
   * @param ast\ParamNode[] $params
   * @return ir\Names
   */
  private static function params(self $ctx, array $params): ir\Names {
    $names = [];

    foreach ($params as $param) {
      $symbol  = $param->name->get('symbol');
      $type    = $ctx->env->read($symbol);
      $text    = $param->name->value;
      $names[] = new ir\Name($type, $text, $symbol);
    }

    return new ir\Names($names);
  }

  /**
   * @param Compiler   $ctx
   * @param ast\Stmt[] $stmts
   * @return ir\Stmt|null
   */
  private static function stmts(self $ctx, array $stmts): ?ir\Stmt {
    array_push($ctx->stmts, null);

    foreach ($stmts as $stmt) {
      self::stmt($ctx, $stmt);
    }

    return array_pop($ctx->stmts);
  }

  private static function stmt(self $ctx, ast\Stmt $stmt): void {
    switch (true) {
      case $stmt instanceof ast\LetStmt:
        self::let_stmt($ctx, $stmt);
        break;
      case $stmt instanceof ast\SemiStmt:
        self::semi_stmt($ctx, $stmt);
        break;
      case $stmt instanceof ast\ExprStmt:
        self::expr_stmt($ctx, $stmt);
        break;
      default:
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  private static function let_stmt(self $ctx, ast\LetStmt $stmt): void {
    $symbol = $stmt->name->get('symbol');
    $type   = $ctx->env->read($symbol);
    $text   = $stmt->name->value;
    $name   = new ir\Name($type, $text, $symbol);
    $expr   = self::expr($ctx, $stmt->expr);
    $let    = new ir\Let($name, $expr, null);
    $ctx->push_stmt($let);
  }

  private static function semi_stmt(self $ctx, ast\SemiStmt $stmt): void {
    $expr = self::expr($ctx, $stmt->expr);
    $let  = new ir\Let(null, $expr, null);
    $ctx->push_stmt($let);
  }

  private static function expr_stmt(self $ctx, ast\ExprStmt $stmt): void {
    $expr = self::expr($ctx, $stmt->expr);
    $ret  = new ir\Ret($expr, null);
    $ctx->push_stmt($ret);
  }

  /**
   * @param Compiler   $ctx
   * @param ast\Expr[] $exprs
   * @return ir\Exprs
   */
  private static function exprs(self $ctx, array $exprs): ir\Exprs {
    $new_exprs = [];

    foreach ($exprs as $expr) {
      $new_exprs[] = self::expr($ctx, $expr);
    }

    return new ir\Exprs($new_exprs);
  }

  private static function expr(self $ctx, ast\Expr $expr): ir\Expr {
    switch (true) {
      case $expr instanceof ast\MatchExpr:
        return self::match_expr($ctx, $expr);
      case $expr instanceof ast\CallExpr:
        return self::call_expr($ctx, $expr);
      case $expr instanceof ast\BinaryExpr:
        return self::binary_expr($ctx, $expr);
      case $expr instanceof ast\VariantConstructorExpr:
        return self::ctor_expr($ctx, $expr);
      case $expr instanceof ast\PathExpr:
        return self::path_expr($ctx, $expr);
      case $expr instanceof ast\StrLiteral:
        return self::str_literal($expr);
      case $expr instanceof ast\FloatLiteral:
        return self::float_literal($expr);
      case $expr instanceof ast\IntLiteral:
        return self::int_literal($expr);
      case $expr instanceof ast\BoolLiteral:
        return self::bool_literal($expr);
      default:
        echo get_class($expr) . PHP_EOL;
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }

    // UnitLiteral
  }

  private static function match_expr(self $ctx, ast\MatchExpr $expr): ir\Match {
    $disc      = self::expr($ctx, $expr->discriminant);
    $disc_type = $expr->discriminant->get('type');
    $out_type  = $expr->get('type');
    assert($disc_type instanceof hm\Type);
    assert($out_type instanceof hm\Type);

    $arms = [];
    foreach ($expr->arms as $arm) {
      $pattern = $arm->pattern;
      $handler = self::expr($ctx, $arm->handler);
      $arms[]  = new ir\Arm($pattern, $handler);
    }

    return new ir\Match($out_type, $disc, $arms);
  }

  private static function call_expr(self $ctx, ast\CallExpr $expr): ir\Apply {
    $callee = self::expr($ctx, $expr->callee);
    $type   = $expr->get('type');
    $args   = self::exprs($ctx, $expr->args);
    return new ir\Apply($type, $callee, $args);
  }

  private static function binary_expr(self $ctx, ast\BinaryExpr $expr): ir\Apply {
    $symbol = $expr->operator->get('symbol');
    $type   = $ctx->env->read($symbol);
    $text   = self::symbol_to_text($symbol);
    $oper   = new ir\NameExpr(new ir\Name($type, $text, $symbol));
    $left   = self::expr($ctx, $expr->left);
    $right  = self::expr($ctx, $expr->right);
    $type   = $expr->get('type');
    $args   = new ir\Exprs([ $left, $right ]);
    return new ir\Apply($type, $oper, $args);
  }

  private static function ctor_expr(self $ctx, ast\VariantConstructorExpr $expr): ir\Ctor {
    $type        = $expr->get('type');
    $form_symbol = $expr->path->tail->get('symbol');
    $args        = self::ctor_args($ctx, $expr->fields);
    return new ir\Ctor($type, $form_symbol, $args);
  }

  private static function ctor_args(self $ctx, ?ast\VariantConstructorFields $fields): ir\Expr {
    if ($fields instanceof ast\NamedVariantConstructorFields) {
      $record_fields = [];
      foreach ($fields->pairs as $pair_name => $pair) {
        $record_fields[$pair_name] = self::expr($ctx, $pair->expr);
      }
      return new ir\Record($fields->get('type'), $record_fields);
    } else if ($fields instanceof ast\OrderedVariantConstructorFields) {
      $tuple_fields = [];
      foreach ($fields->order as $tuple_expr) {
        $tuple_fields[] = self::expr($ctx, $tuple_expr);
      }
      return new ir\Tuple($fields->get('type'), $tuple_fields);
    } else {
      assert($fields === null);
      return new ir\UnitLit();
    }
  }

  private static function path_expr(self $ctx, ast\PathExpr $expr): ir\NameExpr {
    $symbol = $expr->path->tail->get('symbol');
    $type   = $ctx->env->read($symbol);
    $text   = self::symbol_to_text($symbol);
    return new ir\NameExpr(new ir\Name($type, $text, $symbol));
  }

  private static function str_literal(ast\StrLiteral $expr): ir\StrLit {
    return new ir\StrLit($expr->str_value);
  }

  private static function float_literal(ast\FloatLiteral $expr): ir\FloatLit {
    return new ir\FloatLit($expr->float_value);
  }

  private static function int_literal(ast\IntLiteral $expr): ir\IntLit {
    return new ir\IntLit($expr->int_value);
  }

  private static function bool_literal(ast\BoolLiteral $expr): ir\BoolLit {
    return new ir\BoolLit($expr->bool_value);
  }

  private static function symbol_to_text(Symbol $symbol): string {
    if ($symbol instanceof RefSymbol) {
      $path = '';
      do {
        $path = '::' . $symbol->get('text') . $path;
      } while (($symbol = $symbol->parent) !== null);
      return $path;
    } else {
      return $symbol->get('text');
    }
  }
}
