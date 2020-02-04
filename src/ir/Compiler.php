<?php

namespace Cthulhu\ir;

use Cthulhu\ast\nodes as ast;
use Cthulhu\err\Error;
use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\names\VarSymbol;
use Cthulhu\ir\nodes as ir;

class Compiler {
  private ?ir\Module $module = null;

  /* @var ir\Stmt[] $stmts */
  private array $stmts = [];

  /* @var ir\Apply[] $entry_calls */
  private array $entry_calls = [];

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

  /**
   * @param ast\Program $prog
   * @return ir\Root
   * @throws Error
   */
  public static function program(ast\Program $prog): ir\Root {
    $ctx = new self();

    foreach ($prog->files as $file) {
      self::file($ctx, $file);
    }

    if (empty($ctx->entry_calls)) {
      throw Errors::no_main_func();
    } else {
      $entry_stmt = null;
      foreach (array_reverse($ctx->entry_calls) as $entry_call) {
        $entry_stmt = new ir\Let(null, $entry_call, $entry_stmt);
      }

      $entry_mod = new ir\Module(null, $entry_stmt, null);
      $ctx->module->mutable_append($entry_mod);
    }

    return new ir\Root($ctx->module);
  }

  private static function file(self $ctx, ast\File $file): void {
    $symbol = $file->name->get('symbol');
    $text   = self::symbol_to_text($symbol);
    $type   = types\Atomic::unit();
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
      case $item instanceof ast\EnumItem:
        self::enum_item($ctx, $item);
        break;
      case $item instanceof ast\IntrinsicItem:
        self::intrinsic_item($ctx, $item);
        break;
      case $item instanceof ast\FnItem:
        self::fn_item($ctx, $item);
        break;
    }
  }

  private static function enum_item(self $ctx, ast\EnumItem $item): void {
    $enum_symbol = $item->name->get('symbol');
    $enum_type   = $enum_symbol->get(TypeCheck::TYPE_KEY);
    assert($enum_type instanceof types\Enum);

    $forms = [];
    foreach ($item->forms as $form_decl) {
      $form_name   = $form_decl->name->value;
      $form_symbol = $form_decl->name->get('symbol');
      $form_type   = $enum_type->forms[$form_name];
      $form_name   = new ir\Name($form_type, $form_name, $form_symbol);

      if ($form_decl instanceof ast\NamedFormDecl) {
        assert($form_type instanceof types\Record);
        $mapping      = [];
        $symbol_table = [];
        foreach ($form_decl->params as $pair) {
          $symbol_table[$pair->name->value] = $pair->name->get('symbol');
        }
        foreach ($form_type->fields as $field_name => $field_type) {
          $field_symbol         = $symbol_table[$field_name];
          $mapping[$field_name] = new ir\Name($field_type, $field_name, $field_symbol);
        }
        $forms[] = new ir\NamedForm($form_name, $mapping);
      } else if ($form_decl instanceof ast\OrderedFormDecl) {
        assert($form_type instanceof types\Tuple);
        $order   = $form_type->members;
        $forms[] = new ir\OrderedForm($form_name, $order);
      } else {
        assert($form_decl instanceof ast\NullaryFormDecl);
        $forms[] = new ir\NullaryForm($form_name);
      }
    }

    $enum_name = $item->name->value;
    $enum_name = new ir\Name($enum_type, $enum_name, $enum_symbol);
    $enum_stmt = new ir\Enum($enum_name, $forms, null);

    $ctx->push_stmt($enum_stmt);
  }

  private static function intrinsic_item(self $ctx, ast\IntrinsicItem $item): void {
    foreach ($item->signatures as $sig) {
      $symbol = $sig->name->get('symbol');
      $text   = self::symbol_to_text($symbol);
      $type   = $sig->name->get('symbol')->get(TypeCheck::TYPE_KEY);
      assert($type instanceof types\Func);
      $name  = new ir\Name($type, $text, $symbol);
      $ident = $sig->name->value;

      $names      = [];
      $exprs      = [];
      $input_type = $type;
      while ($input_type instanceof types\Func) {
        $name_type   = $input_type->input;
        $name_text   = chr(ord('a') + count($names));
        $name_symbol = (new VarSymbol())->set('text', $name_text);
        $names[]     = $param_name = new ir\Name($name_type, $name_text, $name_symbol);
        $exprs[]     = new ir\NameExpr($param_name);
        $input_type  = $input_type->output;
      }
      $params = new ir\Names($names);
      $args   = new ir\Exprs($exprs);

      $int = new ir\Intrinsic($type, $ident, $args);
      $def = new ir\Def($name, $params, new ir\Ret($int, null), null);
      $ctx->push_stmt($def);
    }
  }

  private static function fn_item(self $ctx, ast\FnItem $item): void {
    if ($item->name instanceof ast\Operator) {
      $symbol = $item->name->get('symbol');
    } else {
      assert($item->name instanceof ast\LowerName);
      $symbol = $item->name->get('symbol');
    }

    $text = self::symbol_to_text($symbol);
    $type = $symbol->get(TypeCheck::TYPE_KEY);
    assert($type instanceof types\Func);

    $name   = new ir\Name($type, $text, $symbol);
    $params = self::params($item->params);
    $body   = self::stmts($ctx, $item->body->stmts);
    $def    = new ir\Def($name, $params, $body, null);

    if (self::is_entry_point($item)) {
      $def->set('entry', true);
      $callee             = new ir\NameExpr($name);
      $args               = new ir\Exprs([ new ir\UnitLit() ]);
      $ctx->entry_calls[] = new ir\Apply($type->output, $callee, $args);
    }

    $ctx->push_stmt($def);
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

  private static function params(ast\FnParams $params): ir\Names {
    $names = [];

    foreach ($params->params as $param) {
      $symbol  = $param->name->get('symbol');
      $type    = $symbol->get(TypeCheck::TYPE_KEY);
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
    $type   = $symbol->get(TypeCheck::TYPE_KEY);
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

  private static function exprs(self $ctx, ast\Exprs $exprs): ir\Exprs {
    $new_exprs = [];

    foreach ($exprs->exprs as $expr) {
      $new_exprs[] = self::expr($ctx, $expr);
    }

    return new ir\Exprs($new_exprs);
  }

  private static function expr(self $ctx, ast\Expr $expr): ir\Expr {
    switch (true) {
      case $expr instanceof ast\MatchExpr:
        return self::match_expr($ctx, $expr);
      case $expr instanceof ast\IfExpr:
        return self::if_expr($ctx, $expr);
      case $expr instanceof ast\CallExpr:
        return self::call_expr($ctx, $expr);
      case $expr instanceof ast\BinaryExpr:
        return self::binary_expr($ctx, $expr);
      case $expr instanceof ast\UnaryExpr:
        return self::unary_expr($ctx, $expr);
      case $expr instanceof ast\VariantConstructorExpr:
        return self::ctor_expr($ctx, $expr);
      case $expr instanceof ast\ListExpr:
        return self::list_expr($ctx, $expr);
      case $expr instanceof ast\PathExpr:
        return self::path_expr($expr);
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
  }

  private static function match_expr(self $ctx, ast\MatchExpr $expr): ir\Match {
    $disc      = self::expr($ctx, $expr->discriminant);
    $disc_type = $expr->discriminant->get(TypeCheck::TYPE_KEY);
    $out_type  = $expr->get(TypeCheck::TYPE_KEY);
    assert($disc_type instanceof types\Type);
    assert($out_type instanceof types\Type);

    $arms = [];
    foreach ($expr->arms as $arm) {
      $pattern = self::pattern($disc_type, $arm->pattern);
      $handler = new ir\Handler(self::expr($ctx, $arm->handler));
      $arms[]  = new ir\Arm($pattern, $handler);
    }
    $arms = new ir\Arms($arms);

    return new ir\Match($out_type, $disc, $arms);
  }

  private static function pattern(types\Type $type, ast\Pattern $pat): ir\Pattern {
    switch (true) {
      case $pat instanceof ast\ConstPattern:
      {
        switch (true) {
          case $pat->literal instanceof ast\StrLiteral:
            return new ir\StrConstPattern($pat->literal->str_value);
          case $pat->literal instanceof ast\FloatLiteral:
            return new ir\FloatConstPattern($pat->literal->float_value);
          case $pat->literal instanceof ast\IntLiteral:
            return new ir\IntConstPattern($pat->literal->int_value);
          case $pat->literal instanceof ast\BoolLiteral:
            return new ir\BoolConstPattern($pat->literal->bool_value);
          default:
            die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
        }
      }
      case $pat instanceof ast\NamedFormPattern:
      {
        assert($type instanceof types\Enum);
        $form_type = $type->forms[$pat->path->tail->value];
        assert($form_type instanceof types\Record);
        $ref_symbol = $pat->path->tail->get('symbol');
        $mapping    = [];
        foreach ($pat->pairs as $pair) {
          $field_symbol         = $pair->name->get('symbol');
          $field_text           = $pair->name->value;
          $field_type           = $form_type->fields[$field_text];
          $field_name           = new ir\Name($field_type, $field_text, $field_symbol);
          $field_pattern        = self::pattern($field_type, $pair->pattern);
          $field                = new ir\NamedFormField($field_name, $field_pattern);
          $mapping[$field_text] = $field;
        }
        return new ir\NamedFormPattern($type, $ref_symbol, $mapping);
      }
      case $pat instanceof ast\OrderedFormPattern:
      {
        assert($type instanceof types\Enum);
        $form_type = $type->forms[$pat->path->tail->value];
        assert($form_type instanceof types\Tuple);
        $ref_symbol = $pat->path->tail->get('symbol');
        $order      = [];
        foreach ($pat->order as $index => $member_pattern) {
          $field_type    = $form_type->members[$index];
          $field_pattern = self::pattern($field_type, $member_pattern);
          $order[$index] = new ir\OrderedFormMember($index, $field_pattern);
        }
        return new ir\OrderedFormPattern($type, $ref_symbol, $order);
      }
      case $pat instanceof ast\NullaryFormPattern:
      {
        assert($type instanceof types\Enum);
        $form_type = $type->forms[$pat->path->tail->value];
        assert($form_type instanceof types\Atomic && $form_type->name === 'Unit');
        $ref_symbol = $pat->path->tail->get('symbol');
        return new ir\NullaryFormPattern($type, $ref_symbol);
      }
      case $pat instanceof ast\ListPattern:
      {
        assert($type instanceof types\ListType);
        $sub_patterns = [];
        foreach ($pat->elements as $index => $sub_pattern) {
          $sub_pattern    = self::pattern($type->elements, $sub_pattern);
          $sub_patterns[] = new ir\ListPatternMember($index, $sub_pattern);
        }

        $glob = null;
        if ($pat->glob) {
          $glob_type    = new types\ListType($type->elements);
          $glob_binding = self::pattern($glob_type, $pat->glob->binding);
          assert($glob_binding instanceof ir\VariablePattern);
          $glob = new ir\Glob(count($sub_patterns), $glob_binding);
        }

        return new ir\ListPattern($type, $sub_patterns, $glob);
      }
      case $pat instanceof ast\VariablePattern:
        return new ir\VariablePattern(new ir\Name($type, $pat->name->value, $pat->name->get('symbol')));
      case $pat instanceof ast\WildcardPattern:
        return new ir\WildcardPattern($type);
      default:
        echo get_class($pat) . PHP_EOL;
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  private static function if_expr(self $ctx, ast\IfExpr $expr): ir\IfExpr {
    $type       = $expr->get(TypeCheck::TYPE_KEY);
    $condition  = self::expr($ctx, $expr->condition);
    $consequent = new ir\Stmts(self::stmts($ctx, $expr->consequent->stmts));
    $alternate  = new ir\Stmts(self::stmts($ctx, $expr->alternate ? $expr->alternate->stmts : []));
    return new ir\IfExpr($type, $condition, $consequent, $alternate);
  }

  private static function call_expr(self $ctx, ast\CallExpr $expr): ir\Apply {
    $callee = self::expr($ctx, $expr->callee);
    $type   = $expr->get(TypeCheck::TYPE_KEY);
    $args   = self::exprs($ctx, $expr->args);
    return new ir\Apply($type, $callee, $args);
  }

  private static function binary_expr(self $ctx, ast\BinaryExpr $expr): ir\Apply {
    $symbol = $expr->operator->oper->get('symbol');
    $type   = $symbol->get(TypeCheck::TYPE_KEY);
    $text   = self::symbol_to_text($symbol);
    $oper   = new ir\NameExpr(new ir\Name($type, $text, $symbol));
    $left   = self::expr($ctx, $expr->left);
    $right  = self::expr($ctx, $expr->right);
    $type   = $expr->get(TypeCheck::TYPE_KEY);
    $args   = new ir\Exprs([ $left, $right ]);
    return new ir\Apply($type, $oper, $args);
  }

  private static function unary_expr(self $ctx, ast\UnaryExpr $expr): ir\Apply {
    $symbol  = $expr->operator->oper->get('symbol');
    $type    = $symbol->get(TypeCheck::TYPE_KEY);
    $text    = self::symbol_to_text($symbol);
    $oper    = new ir\NameExpr(new ir\Name($type, $text, $symbol));
    $operand = self::expr($ctx, $expr->right);
    $type    = $expr->get(TypeCheck::TYPE_KEY);
    $args    = new ir\Exprs([ $operand ]);
    return new ir\Apply($type, $oper, $args);
  }

  private static function ctor_expr(self $ctx, ast\VariantConstructorExpr $expr): ir\Ctor {
    $type = $expr->get(TypeCheck::TYPE_KEY);
    assert($type instanceof types\Enum);
    $form_symbol = $expr->path->tail->get('symbol');
    $form_text   = $expr->path->tail->value;
    $form_type   = $type->forms[$form_text];
    $form_name   = new ir\Name($form_type, $form_text, $form_symbol);
    $args        = self::ctor_args($ctx, $type->forms[(string)$expr->path->tail], $expr->fields);
    return new ir\Ctor($form_name, $args);
  }

  private static function ctor_args(self $ctx, types\Type $type, ?ast\VariantConstructorFields $fields): ir\Expr {
    if ($fields instanceof ast\NamedVariantConstructorFields) {
      $record_fields = [];
      foreach ($fields->pairs as $pair_name => $pair) {
        $field_expr                = self::expr($ctx, $pair->expr);
        $field_name                = new ir\Name($field_expr->type, $pair_name, $pair->name->get('symbol'));
        $record_fields[$pair_name] = new ir\Field($field_name, $field_expr);
      }
      return new ir\Record($type, $record_fields);
    } else if ($fields instanceof ast\OrderedVariantConstructorFields) {
      $tuple_fields = [];
      foreach ($fields->order as $tuple_expr) {
        $tuple_fields[] = self::expr($ctx, $tuple_expr);
      }
      return new ir\Tuple($type, $tuple_fields);
    } else {
      assert($fields === null);
      return new ir\UnitLit();
    }
  }

  private static function list_expr(self $ctx, ast\ListExpr $expr): ir\ListExpr {
    $type     = $expr->get(TypeCheck::TYPE_KEY);
    $elements = [];
    foreach ($expr->elements as $element) {
      $elements[] = self::expr($ctx, $element);
    }
    return new ir\ListExpr($type, $elements);
  }

  private static function path_expr(ast\PathExpr $expr): ir\NameExpr {
    $symbol = $expr->path->tail->get('symbol');
    $type   = $symbol->get(TypeCheck::TYPE_KEY);
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
