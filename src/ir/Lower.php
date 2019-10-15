<?php

namespace Cthulhu\ir;

use Cthulhu\ast;

class Lower {
  public static function file(Table $spans, ast\File $file): nodes\Library {
    $name  = new nodes\Name($file->file->basename());
    $items = self::items($spans, $file->items);
    return $spans->set(new nodes\Library($name, $items), $file->span);
  }

  /**
   * Lower item nodes
   */

  private static function items(Table $spans, array $items): array {
    $_items = [];
    foreach ($items as $item) {
      $_items[] = self::item($spans, $item);
    }
    return $_items;
  }

  private static function item(Table $spans, ast\Item $item): nodes\Item {
    switch (true) {
      case $item instanceof ast\ModItem:
        return self::mod_item($spans, $item);
      case $item instanceof ast\UseItem:
        return self::use_item($spans, $item);
      case $item instanceof ast\FnItem:
        return self::func_item($spans, $item);
      case $item instanceof ast\NativeFuncItem:
        return self::native_item($spans, $item);
      case $item instanceof ast\NativeTypeItem:
        return self::native_type_item($spans, $item);
      default:
        throw new \Exception('cannot lower unknown ast item');
    }
  }

  private static function mod_item(Table $spans, ast\ModItem $item): nodes\ModItem {
    $attrs = self::attrs($item->attrs);
    $name  = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    $items = self::items($spans, $item->items);
    return $spans->set(new nodes\ModItem($name, $items, $attrs), $item->span);
  }

  private static function use_item(Table $spans, ast\UseItem $item): nodes\UseItem {
    $attrs = self::attrs($item->attrs);
    $ref   = self::compound_path($spans, $item->path);
    return $spans->set(new nodes\UseItem($ref, $attrs), $item->span);
  }

  private static function func_item(Table $spans, ast\FnItem $item): nodes\FuncItem {
    $attrs  = self::attrs($item->attrs);
    $name   = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    $polys  = self::func_polys($spans, $item->polys);
    $params = self::func_params($spans, $item->params);
    $output = self::note($spans, $item->returns);
    $body   = self::block($spans, $item->body);
    return $spans->set(new nodes\FuncItem($name, $polys, $params, $output, $body, $attrs), $item->span);
  }

  private static function native_item(Table $spans, ast\NativeFuncItem $item): nodes\NativeFuncItem {
    $attrs = self::attrs($item->attrs);
    $name  = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    $polys = self::func_polys($spans, $item->polys);
    $note  = self::func_note($spans, $item->note);
    return $spans->set(new nodes\NativeFuncItem($name, $polys, $note, $attrs), $item->span);
  }

  private static function native_type_item(Table $spans, ast\NativeTypeItem $item): nodes\NativeTypeItem {
    $attrs = self::attrs($item->attrs);
    $name  = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    return $spans->set(new nodes\NativeTypeItem($name, $attrs), $item->span);
  }

  /**
   * Lower statement nodes
   */

  private static function stmt(Table $spans, ast\Stmt $stmt): nodes\Stmt {
    switch (true) {
      case $stmt instanceof ast\LetStmt:
        return self::let_stmt($spans, $stmt);
      case $stmt instanceof ast\SemiStmt:
        return self::semi_stmt($spans, $stmt);
      case $stmt instanceof ast\ExprStmt:
        return self::expr_stmt($spans, $stmt);
      default:
        throw new \Exception('cannot lower unknown ast statement');
    }
  }

  private static function let_stmt(Table $spans, ast\LetStmt $stmt): nodes\LetStmt {
    $name = $spans->set(new nodes\Name($stmt->name->ident), $stmt->name->span);
    $note = $stmt->note ? self::note($spans, $stmt->note) : null;
    $expr = self::expr($spans, $stmt->expr);
    return $spans->set(new nodes\LetStmt($name, $note, $expr), $stmt->span);
  }

  private static function semi_stmt(Table $spans, ast\SemiStmt $stmt): nodes\SemiStmt {
    $expr = self::expr($spans, $stmt->expr);
    return $spans->set(new nodes\SemiStmt($expr), $stmt->span);
  }

  private static function expr_stmt(Table $spans, ast\ExprStmt $stmt): nodes\ReturnStmt {
    $expr = self::expr($spans, $stmt->expr);
    return $spans->set(new nodes\ReturnStmt($expr), $stmt->span);
  }

  /**
   * Lower expression nodes
   */

  private static function expr(Table $spans, ast\Expr $expr): nodes\Expr {
    switch (true) {
      case $expr instanceof ast\IfExpr:
        return self::if_expr($spans, $expr);
      case $expr instanceof ast\CallExpr:
        return self::call_expr($spans, $expr);
      case $expr instanceof ast\BinaryExpr:
        return self::binary_expr($spans, $expr);
      case $expr instanceof ast\UnaryExpr:
        return self::unary_expr($spans, $expr);
      case $expr instanceof ast\ListExpr:
        return self::list_expr($spans, $expr);
      case $expr instanceof ast\PathExpr:
        return self::path_expr($spans, $expr);
      case $expr instanceof ast\StrExpr:
        return self::str_expr($spans, $expr);
      case $expr instanceof ast\IntExpr:
        return self::int_expr($spans, $expr);
      case $expr instanceof ast\BoolExpr:
        return self::bool_expr($spans, $expr);
      default:
        throw new \Exception('cannot lower unknown ast expression');
    }
  }

  private static function if_expr(Table $spans, ast\IfExpr $expr): nodes\IfExpr {
    $cond     = self::expr($spans, $expr->condition);
    $if_true  = self::block($spans, $expr->if_clause);
    $if_false = $expr->else_clause ? self::block($spans, $expr->else_clause) : null;
    return $spans->set(new nodes\IfExpr($cond, $if_true, $if_false), $expr->span);
  }

  private static function call_expr(Table $spans, ast\CallExpr $expr): nodes\CallExpr {
    $callee = self::expr($spans, $expr->callee);
    $polys = [];
    foreach ($expr->polys as $poly) {
      $polys[] = self::note($spans, $poly);
    }
    $args   = [];
    foreach ($expr->args as $arg) {
      $args[] = self::expr($spans, $arg);
    }
    return $spans->set(new nodes\CallExpr($callee, $polys, $args), $expr->span);
  }

  private static function binary_expr(Table $spans, ast\BinaryExpr $expr): nodes\BinaryExpr {
    $op    = $expr->operator;
    $left  = self::expr($spans, $expr->left);
    $right = self::expr($spans, $expr->right);
    return $spans->set(new nodes\BinaryExpr($op, $left, $right), $expr->span);
  }

  private static function unary_expr(Table $spans, ast\UnaryExpr $expr): nodes\UnaryExpr {
    $op    = $expr->operator;
    $right = self::expr($spans, $expr->operand);
    return $spans->set(new nodes\UnaryExpr($op, $right), $expr->span);
  }

  private static function list_expr(Table $spans, ast\ListExpr $expr): nodes\ListExpr {
    $elements = [];
    foreach ($expr->elements as $element) {
      $elements[] = self::expr($spans, $element);
    }
    return $spans->set(new nodes\ListExpr($elements), $expr->span);
  }

  private static function path_expr(Table $spans, ast\PathExpr $expr): nodes\RefExpr {
    $ref = self::path($spans, $expr->path);
    return $spans->set(new nodes\RefExpr($ref), $expr->span);
  }

  private static function str_expr(Table $spans, ast\StrExpr $expr): nodes\StrExpr {
    $value = $expr->value;
    return $spans->set(new nodes\StrExpr($value), $expr->span);
  }

  private static function int_expr(Table $spans, ast\IntExpr $expr): nodes\IntExpr {
    $value = $expr->value;
    return $spans->set(new nodes\IntExpr($value), $expr->span);
  }

  private static function bool_expr(Table $spans, ast\BoolExpr $expr): nodes\BoolExpr {
    $value = $expr->value;
    return $spans->set(new nodes\BoolExpr($value), $expr->span);
  }

  /**
   * Lower type annotation nodes
   */

  private static function note(Table $spans, ast\Annotation $note): nodes\Note {
    switch (true) {
      case $note instanceof ast\UnitAnnotation:
        return self::unit_note($spans, $note);
      case $note instanceof ast\NamedAnnotation:
        return self::name_note($spans, $note);
      case $note instanceof ast\FunctionAnnotation:
        return self::func_note($spans, $note);
      case $note instanceof ast\ListAnnotation:
        return self::list_note($spans, $note);
      default:
        throw new \Exception('cannot lower unknown type annotation');
    }
  }

  private static function unit_note(Table $spans, ast\UnitAnnotation $note): nodes\UnitNote {
    return $spans->set(new nodes\UnitNote(), $note->span);
  }

  private static function name_note(Table $spans, ast\NamedAnnotation $note): nodes\NameNote {
    $ref = self::path($spans, $note->path);
    return $spans->set(new nodes\NameNote($ref), $note->span);
  }

  private static function func_note(Table $spans, ast\FunctionAnnotation $note): nodes\FuncNote {
    $inputs = [];
    foreach ($note->inputs as $input) {
      $inputs[] = self::note($spans, $input);
    }
    $output = self::note($spans, $note->output);
    return $spans->set(new nodes\FuncNote($inputs, $output), $note->span);
  }

  private static function list_note(Table $spans, ast\ListAnnotation $note): nodes\ListNote {
    $elements = self::note($spans, $note->elements);
    return $spans->set(new nodes\ListNote($elements), $note->span);
  }

  /**
   * Lower miscellaneous nodes
   */

  private static function attrs(array $attrs): array {
    $hash = [];
    foreach ($attrs as $attr) {
      $hash[$attr->name] = true;
    }
    return $hash;
  }

  private static function func_polys(Table $spans, array $polys): array {
    $_polys = [];
    foreach ($polys as $poly) {
      $_polys[] = $spans->set(new nodes\Name($poly->ident), $poly->span);
    }
    return $_polys;
  }

  private static function func_params(Table $spans, array $params): array {
    $_params = [];
    foreach ($params as $param) {
      $name = $spans->set(new nodes\Name($param->name), $param->name->span);
      $note = self::note($spans, $param->note);
      $_params[] = $spans->set(new nodes\FuncParam($name, $note), $param->span);
    }
    return $_params;
  }

  private static function block(Table $spans, ast\BlockNode $block): nodes\Block {
    $stmts = [];
    foreach ($block->stmts as $stmt) {
      $stmts[] = self::stmt($spans, $stmt);
    }
    return $spans->set(new nodes\Block($stmts), $block->span);
  }

  private static function compound_path(Table $spans, ast\CompoundPathNode $path): nodes\CompoundRef {
    $body = [];
    foreach ($path->body as $segment) {
      $body[] = $spans->set(new nodes\Name($segment->ident), $segment->span);
    }

    if ($path->tail instanceof ast\StarSegment) {
      $tail = $spans->set(new nodes\StarRef(), $path->tail->span);
    } else {
      $tail = $spans->set(new nodes\Name($path->tail->ident), $path->tail->span);
    }

    return $spans->set(new nodes\CompoundRef($path->extern, $body, $tail), $path->span);
  }

  private static function path(Table $spans, ast\PathNode $path): nodes\Ref {
    $segments = [];
    foreach ($path->segments as $segment) {
      $segments[] = $spans->set(new nodes\Name($segment->ident), $segment->span);
    }
    $head_segments = array_slice($segments, 0, -1);
    $tail_segment = end($segments);
    return $spans->set(new nodes\Ref($path->extern, $head_segments, $tail_segment), $path->span);
  }
}
