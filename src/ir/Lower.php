<?php

namespace Cthulhu\ir;

use Cthulhu\AST;

class Lower {
  public static function file(Table $spans, AST\File $file): nodes\Library {
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

  private static function item(Table $spans, AST\Item $item): nodes\Item {
    switch (true) {
      case $item instanceof AST\ModItem:
        return self::mod_item($spans, $item);
      case $item instanceof AST\UseItem:
        return self::use_item($spans, $item);
      case $item instanceof AST\FnItem:
        return self::func_item($spans, $item);
      case $item instanceof AST\NativeFuncItem:
        return self::native_item($spans, $item);
      case $item instanceof AST\NativeTypeItem:
        return self::native_type_item($spans, $item);
      default:
        throw new \Exception('cannot lower unknown ast item');
    }
  }

  private static function mod_item(Table $spans, AST\ModItem $item): nodes\ModItem {
    $attrs = self::attrs($item->attrs);
    $name  = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    $items = self::items($spans, $item->items);
    return $spans->set(new nodes\ModItem($name, $items, $attrs), $item->span);
  }

  private static function use_item(Table $spans, AST\UseItem $item): nodes\UseItem {
    $attrs = self::attrs($item->attrs);
    $ref   = self::compound_path($spans, $item->path);
    return $spans->set(new nodes\UseItem($ref, $attrs), $item->span);
  }

  private static function func_item(Table $spans, AST\FnItem $item): nodes\FuncItem {
    $attrs  = self::attrs($item->attrs);
    $name   = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    $params = self::func_params($spans, $item->params);
    $output = self::note($spans, $item->returns);
    $body   = self::block($spans, $item->body);
    return $spans->set(new nodes\FuncItem($name, $params, $output, $body, $attrs), $item->span);
  }

  private static function native_item(Table $spans, AST\NativeFuncItem $item): nodes\NativeFuncItem {
    $attrs = self::attrs($item->attrs);
    $name  = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    $note  = self::func_note($spans, $item->note);
    return $spans->set(new nodes\NativeFuncItem($name, $note, $attrs), $item->span);
  }

  private static function native_type_item(Table $spans, AST\NativeTypeItem $item): nodes\NativeTypeItem {
    $attrs = self::attrs($item->attrs);
    $name  = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    return $spans->set(new nodes\NativeTypeItem($name, $attrs), $item->span);
  }

  /**
   * Lower statement nodes
   */

  private static function stmt(Table $spans, AST\Stmt $stmt): nodes\Stmt {
    switch (true) {
      case $stmt instanceof AST\LetStmt:
        return self::let_stmt($spans, $stmt);
      case $stmt instanceof AST\SemiStmt:
        return self::semi_stmt($spans, $stmt);
      case $stmt instanceof AST\ExprStmt:
        return self::expr_stmt($spans, $stmt);
      default:
        throw new \Exception('cannot lower unknown ast statement');
    }
  }

  private static function let_stmt(Table $spans, AST\LetStmt $stmt): nodes\LetStmt {
    $name = $spans->set(new nodes\Name($stmt->name->ident), $stmt->name->span);
    $note = $stmt->note ? self::note($spans, $stmt->note) : null;
    $expr = self::expr($spans, $stmt->expr);
    return $spans->set(new nodes\LetStmt($name, $note, $expr), $stmt->span);
  }

  private static function semi_stmt(Table $spans, AST\SemiStmt $stmt): nodes\SemiStmt {
    $expr = self::expr($spans, $stmt->expr);
    return $spans->set(new nodes\SemiStmt($expr), $stmt->span);
  }

  private static function expr_stmt(Table $spans, AST\ExprStmt $stmt): nodes\ReturnStmt {
    $expr = self::expr($spans, $stmt->expr);
    return $spans->set(new nodes\ReturnStmt($expr), $stmt->span);
  }

  /**
   * Lower expression nodes
   */

  private static function expr(Table $spans, AST\Expr $expr): nodes\Expr {
    switch (true) {
      case $expr instanceof AST\IfExpr:
        return self::if_expr($spans, $expr);
      case $expr instanceof AST\CallExpr:
        return self::call_expr($spans, $expr);
      case $expr instanceof AST\BinaryExpr:
        return self::binary_expr($spans, $expr);
      case $expr instanceof AST\UnaryExpr:
        return self::unary_expr($spans, $expr);
      case $expr instanceof AST\PathExpr:
        return self::path_expr($spans, $expr);
      case $expr instanceof AST\StrExpr:
        return self::str_expr($spans, $expr);
      case $expr instanceof AST\NumExpr:
        return self::num_expr($spans, $expr);
      case $expr instanceof AST\BoolExpr:
        return self::bool_expr($spans, $expr);
      default:
        throw new \Exception('cannot lower unknown ast expression');
    }
  }

  private static function if_expr(Table $spans, AST\IfExpr $expr): nodes\IfExpr {
    $cond     = self::expr($spans, $expr->condition);
    $if_true  = self::block($spans, $expr->if_clause);
    $if_false = $expr->else_clause ? self::block($spans, $expr->else_clause) : null;
    return $spans->set(new nodes\IfExpr($cond, $if_true, $if_false), $expr->span);
  }

  private static function call_expr(Table $spans, AST\CallExpr $expr): nodes\CallExpr {
    $callee = self::expr($spans, $expr->callee);
    $args   = [];
    foreach ($expr->args as $arg) {
      $args[] = self::expr($spans, $arg);
    }
    return $spans->set(new nodes\CallExpr($callee, $args), $expr->span);
  }

  private static function binary_expr(Table $spans, AST\BinaryExpr $expr): nodes\BinaryExpr {
    $op    = $expr->operator;
    $left  = self::expr($spans, $expr->left);
    $right = self::expr($spans, $expr->right);
    return $spans->set(new nodes\BinaryExpr($op, $left, $right), $expr->span);
  }

  private static function unary_expr(Table $spans, AST\UnaryExpr $expr): nodes\UnaryExpr {
    $op    = $expr->operator;
    $right = self::expr($spans, $expr->operand);
    return $spans->set(new nodes\UnaryExpr($op, $right), $expr->span);
  }

  private static function path_expr(Table $spans, AST\PathExpr $expr): nodes\RefExpr {
    $ref = self::path($spans, $expr->path);
    return $spans->set(new nodes\RefExpr($ref), $expr->span);
  }

  private static function str_expr(Table $spans, AST\StrExpr $expr): nodes\StrExpr {
    $value = $expr->value;
    return $spans->set(new nodes\StrExpr($value), $expr->span);
  }

  private static function num_expr(Table $spans, AST\NumExpr $expr): nodes\NumExpr {
    $value = $expr->value;
    return $spans->set(new nodes\NumExpr($value), $expr->span);
  }

  private static function bool_expr(Table $spans, AST\BoolExpr $expr): nodes\BoolExpr {
    $value = $expr->value;
    return $spans->set(new nodes\BoolExpr($value), $expr->span);
  }

  /**
   * Lower type annotation nodes
   */

  private static function note(Table $spans, AST\Annotation $note): nodes\Note {
    switch (true) {
      case $note instanceof AST\UnitAnnotation:
        return self::unit_note($spans, $note);
      case $note instanceof AST\NamedAnnotation:
        return self::name_note($spans, $note);
      case $note instanceof AST\FunctionAnnotation:
        return self::func_note($spans, $note);
      default:
        throw new \Exception('cannot lower unknown type annotation');
    }
  }

  private static function unit_note(Table $spans, AST\UnitAnnotation $note): nodes\UnitNote {
    return $spans->set(new nodes\UnitNote(), $note->span);
  }

  private static function name_note(Table $spans, AST\NamedAnnotation $note): nodes\NameNote {
    $ref = self::path($spans, $note->path);
    return $spans->set(new nodes\NameNote($ref), $note->span);
  }

  private static function func_note(Table $spans, AST\FunctionAnnotation $note): nodes\FuncNote {
    $inputs = [];
    foreach ($note->inputs as $input) {
      $inputs[] = self::note($spans, $input);
    }
    $output = self::note($spans, $note->output);
    return $spans->set(new nodes\FuncNote($inputs, $output), $note->span);
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

  private static function func_params(Table $spans, array $params): array {
    $_params = [];
    foreach ($params as $param) {
      $name = $spans->set(new nodes\Name($param->name), $param->name->span);
      $note = self::note($spans, $param->note);
      $_params[] = $spans->set(new nodes\FuncParam($name, $note), $param->span);
    }
    return $_params;
  }

  private static function block(Table $spans, AST\BlockNode $block): nodes\Block {
    $stmts = [];
    foreach ($block->stmts as $stmt) {
      $stmts[] = self::stmt($spans, $stmt);
    }
    return $spans->set(new nodes\Block($stmts), $block->span);
  }

  private static function compound_path(Table $spans, AST\CompoundPathNode $path): nodes\CompoundRef {
    $body = [];
    foreach ($path->body as $segment) {
      $body[] = $spans->set(new nodes\Name($segment->ident), $segment->span);
    }

    if ($path->tail instanceof AST\StarSegment) {
      $tail = $spans->set(new nodes\StarRef(), $path->tail->span);
    } else {
      $tail = $spans->set(new nodes\Name($path->tail->ident), $path->tail->span);
    }

    return $spans->set(new nodes\CompoundRef($path->extern, $body, $tail), $path->span);
  }

  private static function path(Table $spans, AST\PathNode $path): nodes\Ref {
    $segments = [];
    foreach ($path->segments as $segment) {
      $segments[] = $spans->set(new nodes\Name($segment->ident), $segment->span);
    }
    $head_segments = array_slice($segments, 0, -1);
    $tail_segment = end($segments);
    return $spans->set(new nodes\Ref($path->extern, $head_segments, $tail_segment), $path->span);
  }
}
