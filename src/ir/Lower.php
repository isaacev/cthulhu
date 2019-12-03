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
      case $item instanceof ast\UnionItem:
        return self::union_item($spans, $item);
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
    $params = self::func_params($spans, $item->params);
    $output = self::note($spans, $item->returns);
    $body   = self::block($spans, $item->body);
    $head   = $spans->set(
      new nodes\FuncHead($name, $params, $output),
      $item->span->extended_to($item->returns->span));
    return $spans->set(new nodes\FuncItem($head, $body, $attrs), $item->span);
  }

  private static function native_item(Table $spans, ast\NativeFuncItem $item): nodes\NativeFuncItem {
    $attrs = self::attrs($item->attrs);
    $name  = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    $note  = self::func_note($spans, $item->note);
    return $spans->set(new nodes\NativeFuncItem($name, $note, $attrs), $item->span);
  }

  private static function native_type_item(Table $spans, ast\NativeTypeItem $item): nodes\NativeTypeItem {
    $attrs = self::attrs($item->attrs);
    $name  = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    return $spans->set(new nodes\NativeTypeItem($name, $attrs), $item->span);
  }

  private static function union_item(Table $spans, ast\UnionItem $item): nodes\UnionItem {
    $attrs    = self::attrs($item->attrs);
    $name     = $spans->set(new nodes\Name($item->name->ident), $item->name->span);
    $params   = [];
    foreach ($item->params as $param) {
      $params[] = self::param_note($spans, $param);
    }
    $variants = self::union_variants($spans, $item->variants);
    return $spans->set(new nodes\UnionItem($attrs, $name, $params, $variants), $item->span);
  }

  private static function union_variants(Table $spans, array $variants): array {
    return array_map(function ($variant) use ($spans) {
      return self::union_variant_decl($spans, $variant);
    }, $variants);
  }

  private static function union_variant_decl(Table $spans, ast\VariantDeclNode $variant): nodes\VariantDeclNode {
    switch (true) {
      case $variant instanceof ast\NamedVariantDeclNode:
        return self::named_variant_decl($spans, $variant);
      case $variant instanceof ast\OrderedVariantDeclNode:
        return self::ordered_variant_decl($spans, $variant);
      default:
        return self::unit_variant_decl($spans, $variant);
    }
  }

  private static function named_variant_decl(Table $spans, ast\NamedVariantDeclNode $variant): nodes\NamedVariantDeclNode {
    $name   = $spans->set(new nodes\Name($variant->name->ident), $variant->name->span);
    $fields = [];
    foreach ($variant->fields as $field) {
      $field_name = $spans->set(new nodes\Name($field->name->ident), $field->name->span);
      $field_note = self::note($spans, $field->note);
      $fields[]   = $spans->set(new nodes\FieldDeclNode($field_name, $field_note), $field->span);
    }
    return $spans->set(new nodes\NamedVariantDeclNode($name, $fields), $variant->span);
  }

  private static function ordered_variant_decl(Table $spans, ast\OrderedVariantDeclNode $variant): nodes\OrderedVariantDeclNode {
    $name    = $spans->set(new nodes\Name($variant->name->ident), $variant->name->span);
    $members = [];
    foreach ($variant->members as $member) {
      $members[] = self::note($spans, $member);
    }
    return $spans->set(new nodes\OrderedVariantDeclNode($name, $members), $variant->span);
  }

  private static function unit_variant_decl(Table $spans, ast\UnitVariantDeclNode $variant): nodes\UnitVariantDeclNode {
    $name = $spans->set(new nodes\Name($variant->name->ident), $variant->name->span);
    return $spans->set(new nodes\UnitVariantDeclNode($name), $variant->span);
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
      case $expr instanceof ast\MatchExpr:
        return self::match_expr($spans, $expr);
      case $expr instanceof ast\IfExpr:
        return self::if_expr($spans, $expr);
      case $expr instanceof ast\CallExpr:
        return self::call_expr($spans, $expr);
      case $expr instanceof ast\VariantConstructorExpr:
        return self::variant_constructor_expr($spans, $expr);
      case $expr instanceof ast\BinaryExpr:
        return self::binary_expr($spans, $expr);
      case $expr instanceof ast\UnaryExpr:
        return self::unary_expr($spans, $expr);
      case $expr instanceof ast\ListExpr:
        return self::list_expr($spans, $expr);
      case $expr instanceof ast\PathExpr:
        return self::path_expr($spans, $expr);
      case $expr instanceof ast\Literal:
        return self::literal($spans, $expr);
      default:
        throw new \Exception('cannot lower unknown ast expression');
    }
  }

  private static function match_expr(Table $spans, ast\MatchExpr $expr): nodes\MatchExpr {
    $disc = $spans->set(new nodes\MatchDiscriminant(self::expr($spans, $expr->disc)), $expr->disc->span);
    $arms = [];
    foreach ($expr->arms as $arm) {
      $arms[] = self::match_arm($spans, $arm);
    }
    return $spans->set(new nodes\MatchExpr($disc, $arms), $expr->span);
  }

  private static function match_arm(Table $spans, ast\MatchArm $arm): nodes\MatchArm {
    $pattern = self::pattern($spans, $arm->pattern);
    $handler = $spans->set(
      new nodes\MatchHandler(
        new nodes\ReturnStmt(
          self::expr($spans, $arm->handler)
        )
      ),
      $arm->handler->span
    );
    return $spans->set(new nodes\MatchArm($pattern, $handler), $arm->span);
  }

  private static function pattern(Table $spans, ast\Pattern $pattern): nodes\Pattern {
    switch (true) {
      case $pattern instanceof ast\VariantPattern:
        return self::variant_pattern($spans, $pattern);
      case $pattern instanceof ast\VariablePattern:
        return self::variable_pattern($spans, $pattern);
      case $pattern instanceof ast\ConstPattern:
        return self::const_pattern($spans, $pattern);
      case $pattern instanceof ast\WildcardPattern:
        return self::wildcard_pattern($spans, $pattern);
      default:
        assert(false, 'unreachable');
    }
  }

  private static function variant_pattern(Table $spans, ast\VariantPattern $pattern): nodes\VariantPattern {
    $ref = self::path($spans, $pattern->path);
    $fields = self::variant_pattern_fields($spans, $pattern->fields);
    return $spans->set(new nodes\VariantPattern($ref, $fields), $pattern->span);
  }

  private static function variant_pattern_fields(Table $spans, ?ast\VariantPatternFields $fields): ?nodes\VariantPatternFields {
    switch (true) {
      case $fields instanceof ast\NamedVariantPatternFields:
        return self::named_variant_pattern_fields($spans, $fields);
      case $fields instanceof ast\OrderedVariantPatternFields:
        return self::ordered_variant_pattern_fields($spans, $fields);
      default:
        return null;
    }
  }

  private static function named_variant_pattern_fields(Table $spans, ast\NamedVariantPatternFields $fields): nodes\NamedVariantPatternFields {
    $mapping = [];
    foreach ($fields->mapping as $field) {
      $name = $field->name->ident;
      if (array_key_exists($name, $mapping)) {
        $first = $spans->get($mapping[$name]->name);
        $second = $field->name->span;
        throw Errors::redundant_named_fields($first, $second, $name);
      }
      $mapping[$name] = self::named_pattern_field($spans, $field);
    }
    return $spans->set(new nodes\NamedVariantPatternFields($mapping), $fields->span);
  }

  private static function named_pattern_field(Table $spans, ast\NamedPatternField $field): nodes\NamedPatternField {
    $name = $spans->set(new nodes\Name($field->name->ident), $field->name->span);
    $pattern = self::pattern($spans, $field->pattern);
    return $spans->set(new nodes\NamedPatternField($name, $pattern), $field->span);
  }

  private static function ordered_variant_pattern_fields(Table $spans, ast\OrderedVariantPatternFields $fields): nodes\OrderedVariantPatternFields {
    $order = [];
    foreach ($fields->order as $index => $pattern) {
      $order[$index] = $spans->set(new nodes\OrderedVariantPatternField($index, self::pattern($spans, $pattern)), $pattern->span);
    }
    return $spans->set(new nodes\OrderedVariantPatternFields($order), $fields->span);
  }

  private static function variable_pattern(Table $spans, ast\VariablePattern $pattern): nodes\VariablePattern {
    $name = $spans->set(new nodes\Name($pattern->name->ident), $pattern->name->span);
    return $spans->set(new nodes\VariablePattern($name), $pattern->span);
  }

  private static function const_pattern(Table $spans, ast\ConstPattern $pattern): nodes\ConstPattern {
    $literal = $pattern->literal;
    switch (true) {
      case $literal instanceof ast\StrLiteral:
        return $spans->set(new nodes\StrConstPattern($literal->value), $pattern->span);
      case $literal instanceof ast\FloatLiteral:
        return $spans->set(new nodes\FloatConstPattern($literal->value), $pattern->span);
      case $literal instanceof ast\IntLiteral:
        return $spans->set(new nodes\IntConstPattern($literal->value), $pattern->span);
      case $literal instanceof ast\BoolLiteral:
        return $spans->set(new nodes\BoolConstPattern($literal->value), $pattern->span);
      default:
        assert(false, 'unreachable');
    }
  }

  private static function wildcard_pattern(Table $spans, ast\WildcardPattern $pattern): nodes\WildcardPattern {
    return $spans->set(new nodes\WildcardPattern(), $pattern->span);
  }

  private static function if_expr(Table $spans, ast\IfExpr $expr): nodes\IfExpr {
    $cond     = self::expr($spans, $expr->condition);
    $if_true  = self::block($spans, $expr->if_clause);
    $if_false = $expr->else_clause ? self::block($spans, $expr->else_clause) : null;
    return $spans->set(new nodes\IfExpr($cond, $if_true, $if_false), $expr->span);
  }

  private static function call_expr(Table $spans, ast\CallExpr $expr): nodes\CallExpr {
    $callee = self::expr($spans, $expr->callee);
    $args   = [];
    foreach ($expr->args as $arg) {
      $args[] = self::expr($spans, $arg);
    }
    return $spans->set(new nodes\CallExpr($callee, $args), $expr->span);
  }

  private static function variant_constructor_expr(Table $spans, ast\VariantConstructorExpr $expr): nodes\VariantConstructorExpr {
    $ref = self::path($spans, $expr->path);
    if ($expr->fields instanceof ast\NamedVariantConstructorFields) {
      $pairs = [];
      foreach ($expr->fields->pairs as $field) {
        $pairs[] = self::field_expr_node($spans, $field);
      }
      $fields = $spans->set(new nodes\NamedVariantConstructorFields($pairs), $expr->fields->span);
    } else if ($expr->fields instanceof ast\OrderedVariantConstructorFields) {
      $order = [];
      foreach ($expr->fields->order as $sub_expr) {
        $order[] = self::expr($spans, $sub_expr);
      }
      $fields = $spans->set(new nodes\OrderedVariantConstructorFields($order), $expr->fields->span);
    } else {
      $fields = null;
    }

    return $spans->set(new nodes\VariantConstructorExpr($ref, $fields), $expr->span);
  }

  private static function field_expr_node(Table $spans, ast\FieldExprNode $node): nodes\FieldExprNode {
    $name = $spans->set(new nodes\Name($node->name->ident), $node->name->span);
    $expr = self::expr($spans, $node->expr);
    return $spans->set(new nodes\FieldExprNode($name, $expr), $node->span);
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

  private static function literal(Table $spans, ast\Literal $literal): nodes\Literal {
    switch (true) {
      case $literal instanceof ast\StrLiteral:
        return self::str_literal($spans, $literal);
      case $literal instanceof ast\FloatLiteral:
        return self::float_literal($spans, $literal);
      case $literal instanceof ast\IntLiteral:
        return self::int_literal($spans, $literal);
      case $literal instanceof ast\BoolLiteral:
        return self::bool_literal($spans, $literal);
      default:
        assert(false, 'unreachable');
    }
  }

  private static function str_literal(Table $spans, ast\StrLiteral $expr): nodes\StrLiteral {
    $value = $expr->value;
    return $spans->set(new nodes\StrLiteral($value), $expr->span);
  }

  private static function float_literal(Table $spans, ast\FloatLiteral $expr): nodes\FloatLiteral {
    $value = $expr->value;
    return $spans->set(new nodes\FloatLiteral($value, $expr->precision), $expr->span);
  }

  private static function int_literal(Table $spans, ast\IntLiteral $expr): nodes\IntLiteral {
    $value = $expr->value;
    return $spans->set(new nodes\IntLiteral($value), $expr->span);
  }

  private static function bool_literal(Table $spans, ast\BoolLiteral $expr): nodes\BoolLiteral {
    $value = $expr->value;
    return $spans->set(new nodes\BoolLiteral($value), $expr->span);
  }

  /**
   * Lower type annotation nodes
   */

  private static function note(Table $spans, ast\Annotation $note): nodes\Note {
    switch (true) {
      case $note instanceof ast\TypeParamAnnotation:
        return self::param_note($spans, $note);
      case $note instanceof ast\UnitAnnotation:
        return self::unit_note($spans, $note);
      case $note instanceof ast\NamedAnnotation:
        return self::name_note($spans, $note);
      case $note instanceof ast\FunctionAnnotation:
        return self::func_note($spans, $note);
      case $note instanceof ast\ListAnnotation:
        return self::list_note($spans, $note);
      case $note instanceof ast\ParameterizedAnnotation:
        return self::parameterized_note($spans, $note);
      default:
        throw new \Exception('cannot lower unknown type annotation');
    }
  }

  private static function param_note(Table $spans, ast\TypeParamAnnotation $note): nodes\ParamNote {
    $name = $spans->set(new nodes\Name($note->name), $note->span); // TODO: this span should not include the left quote
    return $spans->set(new nodes\ParamNote($name), $note->span);
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
    if ($note->elements) {
      $elements = self::note($spans, $note->elements);
    } else {
      $elements = null;
    }
    return $spans->set(new nodes\ListNote($elements), $note->span);
  }

  private static function parameterized_note(Table $spans, ast\ParameterizedAnnotation $note): nodes\ParameterizedNote {
    $inner = self::note($spans, $note->inner);
    $params = [];
    foreach ($note->params as $param) {
      $params[] = self::note($spans, $param);
    }
    return $spans->set(new nodes\ParameterizedNote($inner, $params), $note->span);
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
      $name = $spans->set(new nodes\Name($param->name->ident), $param->name->span);
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
    $head = [];
    foreach ($path->head as $segment) {
      $head[] = $spans->set(new nodes\Name($segment->ident), $segment->span);
    }
    $tail = $spans->set(new nodes\Name($path->tail->ident), $path->tail->span);
    return $spans->set(new nodes\Ref($path->extern, $head, $tail), $path->span);
  }
}
