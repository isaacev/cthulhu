<?php

namespace Cthulhu\ir;

use Cthulhu\ast;

class Lower {
  public static function file(ast\File $file): nodes\Library {
    $name  = new nodes\Name($file->file->basename());
    $items = self::items($file->items);
    return (new nodes\Library($name, $items))->set('span', $file->span);
  }

  /**
   * Lower item nodes
   */

  /**
   * @param ast\Item[] $items
   * @return nodes\Item[]
   */
  private static function items(array $items): array {
    $_items = [];
    foreach ($items as $item) {
      $_items[] = self::item($item);
    }
    return $_items;
  }

  private static function item(ast\Item $item): nodes\Item {
    switch (true) {
      case $item instanceof ast\ModItem:
        return self::mod_item($item);
      case $item instanceof ast\UseItem:
        return self::use_item($item);
      case $item instanceof ast\FnItem:
        return self::func_item($item);
      case $item instanceof ast\NativeFuncItem:
        return self::native_item($item);
      case $item instanceof ast\NativeTypeItem:
        return self::native_type_item($item);
      case $item instanceof ast\UnionItem:
        return self::union_item($item);
      default:
        die('unreachable');
    }
  }

  private static function mod_item(ast\ModItem $item): nodes\ModItem {
    $attrs = self::attrs($item->attrs);
    $name  = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    $items = self::items($item->items);
    return (new nodes\ModItem($name, $items, $attrs))->set('span', $item->span);
  }

  private static function use_item(ast\UseItem $item): nodes\UseItem {
    $attrs = self::attrs($item->attrs);
    $ref   = self::compound_path($item->path);
    return (new nodes\UseItem($ref, $attrs))->set('span', $item->span);
  }

  private static function func_item(ast\FnItem $item): nodes\FuncItem {
    $attrs  = self::attrs($item->attrs);
    $name   = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    $params = self::func_params($item->params);
    $output = self::note($item->returns);
    $body   = self::block($item->body);
    $head   = (new nodes\FuncHead($name, $params, $output))->set('span', $item->span->extended_to($item->returns->span));
    return (new nodes\FuncItem($head, $body, $attrs))->set('span', $item->span);
  }

  private static function native_item(ast\NativeFuncItem $item): nodes\NativeFuncItem {
    $attrs = self::attrs($item->attrs);
    $name  = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    $note  = self::func_note($item->note);
    return (new nodes\NativeFuncItem($name, $note, $attrs))->set('span', $item->span);
  }

  private static function native_type_item(ast\NativeTypeItem $item): nodes\NativeTypeItem {
    $attrs = self::attrs($item->attrs);
    $name  = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    return (new nodes\NativeTypeItem($name, $attrs))->set('span', $item->span);
  }

  private static function union_item(ast\UnionItem $item): nodes\UnionItem {
    $attrs  = self::attrs($item->attrs);
    $name   = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    $params = [];
    foreach ($item->params as $param) {
      $params[] = self::param_note($param);
    }
    $variants = self::union_variants($item->variants);
    return (new nodes\UnionItem($attrs, $name, $params, $variants))->set('span', $item->span);
  }

  /**
   * @param ast\VariantDeclNode[] $variants
   * @return nodes\VariantDeclNode[]
   */
  private static function union_variants(array $variants): array {
    return array_map(function ($variant) {
      return self::union_variant_decl($variant);
    }, $variants);
  }

  private static function union_variant_decl(ast\VariantDeclNode $variant): nodes\VariantDeclNode {
    switch (true) {
      case $variant instanceof ast\NamedVariantDeclNode:
        return self::named_variant_decl($variant);
      case $variant instanceof ast\OrderedVariantDeclNode:
        return self::ordered_variant_decl($variant);
      case $variant instanceof ast\UnitVariantDeclNode:
        return self::unit_variant_decl($variant);
      default:
        die('unreachable');
    }
  }

  private static function named_variant_decl(ast\NamedVariantDeclNode $variant): nodes\NamedVariantDeclNode {
    $name   = (new nodes\Name($variant->name->ident))->set('span', $variant->name->span);
    $fields = [];
    foreach ($variant->fields as $field) {
      $field_name = (new nodes\Name($field->name->ident))->set('span', $field->name->span);
      $field_note = self::note($field->note);
      $fields[]   = (new nodes\FieldDeclNode($field_name, $field_note))->set('span', $field->span);
    }
    return (new nodes\NamedVariantDeclNode($name, $fields))->set('span', $variant->span);
  }

  private static function ordered_variant_decl(ast\OrderedVariantDeclNode $variant): nodes\OrderedVariantDeclNode {
    $name    = (new nodes\Name($variant->name->ident))->set('span', $variant->name->span);
    $members = [];
    foreach ($variant->members as $member) {
      $members[] = self::note($member);
    }
    return (new nodes\OrderedVariantDeclNode($name, $members))->set('span', $variant->span);
  }

  private static function unit_variant_decl(ast\UnitVariantDeclNode $variant): nodes\UnitVariantDeclNode {
    $name = (new nodes\Name($variant->name->ident))->set('span', $variant->name->span);
    return (new nodes\UnitVariantDeclNode($name))->set('span', $variant->span);
  }

  private static function stmt(ast\Stmt $stmt): nodes\Stmt {
    switch (true) {
      case $stmt instanceof ast\LetStmt:
        return self::let_stmt($stmt);
      case $stmt instanceof ast\SemiStmt:
        return self::semi_stmt($stmt);
      case $stmt instanceof ast\ExprStmt:
        return self::expr_stmt($stmt);
      default:
        die('unreachable');
    }
  }

  private static function let_stmt(ast\LetStmt $stmt): nodes\LetStmt {
    $name = (new nodes\Name($stmt->name->ident))->set('span', $stmt->name->span);
    $note = $stmt->note ? self::note($stmt->note) : null;
    $expr = self::expr($stmt->expr);
    return (new nodes\LetStmt($name, $note, $expr))->set('span', $stmt->span);
  }

  private static function semi_stmt(ast\SemiStmt $stmt): nodes\SemiStmt {
    $expr = self::expr($stmt->expr);
    return (new nodes\SemiStmt($expr))->set('span', $stmt->span);
  }

  private static function expr_stmt(ast\ExprStmt $stmt): nodes\ReturnStmt {
    $expr = self::expr($stmt->expr);
    return (new nodes\ReturnStmt($expr))->set('span', $stmt->span);
  }

  private static function expr(ast\Expr $expr): nodes\Expr {
    switch (true) {
      case $expr instanceof ast\MatchExpr:
        return self::match_expr($expr);
      case $expr instanceof ast\IfExpr:
        return self::if_expr($expr);
      case $expr instanceof ast\CallExpr:
        return self::call_expr($expr);
      case $expr instanceof ast\VariantConstructorExpr:
        return self::variant_constructor_expr($expr);
      case $expr instanceof ast\BinaryExpr:
        return self::binary_expr($expr);
      case $expr instanceof ast\UnaryExpr:
        return self::unary_expr($expr);
      case $expr instanceof ast\ListExpr:
        return self::list_expr($expr);
      case $expr instanceof ast\PathExpr:
        return self::path_expr($expr);
      case $expr instanceof ast\Literal:
        return self::literal($expr);
      default:
        die('unreachable');
    }
  }

  private static function match_expr(ast\MatchExpr $expr): nodes\MatchExpr {
    $disc = (new nodes\MatchDiscriminant(self::expr($expr->disc)))->set('span', $expr->disc->span);
    $arms = [];
    foreach ($expr->arms as $arm) {
      $arms[] = self::match_arm($arm);
    }
    return (new nodes\MatchExpr($disc, $arms))->set('span', $expr->span);
  }

  private static function match_arm(ast\MatchArm $arm): nodes\MatchArm {
    $pattern = self::pattern($arm->pattern);
    $handler = (new nodes\MatchHandler(new nodes\ReturnStmt(self::expr($arm->handler))))->set('span', $arm->handler->span);
    return (new nodes\MatchArm($pattern, $handler))->set('span', $arm->span);
  }

  private static function pattern(ast\Pattern $pattern): nodes\Pattern {
    switch (true) {
      case $pattern instanceof ast\VariantPattern:
        return self::variant_pattern($pattern);
      case $pattern instanceof ast\VariablePattern:
        return self::variable_pattern($pattern);
      case $pattern instanceof ast\ConstPattern:
        return self::const_pattern($pattern);
      case $pattern instanceof ast\WildcardPattern:
        return self::wildcard_pattern($pattern);
      default:
        die('unreachable');
    }
  }

  private static function variant_pattern(ast\VariantPattern $pattern): nodes\VariantPattern {
    $ref    = self::path($pattern->path);
    $fields = self::variant_pattern_fields($pattern->fields);
    return (new nodes\VariantPattern($ref, $fields))->set('span', $pattern->span);
  }

  private static function variant_pattern_fields(?ast\VariantPatternFields $fields): ?nodes\VariantPatternFields {
    switch (true) {
      case $fields instanceof ast\NamedVariantPatternFields:
        return self::named_variant_pattern_fields($fields);
      case $fields instanceof ast\OrderedVariantPatternFields:
        return self::ordered_variant_pattern_fields($fields);
      default:
        return null;
    }
  }

  private static function named_variant_pattern_fields(ast\NamedVariantPatternFields $fields): nodes\NamedVariantPatternFields {
    $mapping = [];
    foreach ($fields->mapping as $field) {
      $name = $field->name->ident;
      if (array_key_exists($name, $mapping)) {
        $first  = $mapping[$name]->name->get('span');
        $second = $field->name->span;
        throw Errors::redundant_named_fields($first, $second, $name);
      }
      $mapping[$name] = self::named_pattern_field($field);
    }
    return (new nodes\NamedVariantPatternFields($mapping))->set('span', $fields->span);
  }

  private static function named_pattern_field(ast\NamedPatternField $field): nodes\NamedPatternField {
    $name    = (new nodes\Name($field->name->ident))->set('span', $field->name->span);
    $pattern = self::pattern($field->pattern);
    return (new nodes\NamedPatternField($name, $pattern))->set('span', $field->span);
  }

  private static function ordered_variant_pattern_fields(ast\OrderedVariantPatternFields $fields): nodes\OrderedVariantPatternFields {
    $order = [];
    foreach ($fields->order as $index => $pattern) {
      $order[$index] = (new nodes\OrderedVariantPatternField($index, self::pattern($pattern)))->set('span', $pattern->span);
    }
    return (new nodes\OrderedVariantPatternFields($order))->set('span', $fields->span);
  }

  private static function variable_pattern(ast\VariablePattern $pattern): nodes\VariablePattern {
    $name = (new nodes\Name($pattern->name->ident))->set('span', $pattern->name->span);
    return (new nodes\VariablePattern($name))->set('span', $pattern->span);
  }

  private static function const_pattern(ast\ConstPattern $pattern): nodes\ConstPattern {
    $literal = $pattern->literal;
    switch (true) {
      case $literal instanceof ast\StrLiteral:
        return (new nodes\StrConstPattern($literal->value))->set('span', $pattern->span);
      case $literal instanceof ast\FloatLiteral:
        return (new nodes\FloatConstPattern($literal->value))->set('span', $pattern->span);
      case $literal instanceof ast\IntLiteral:
        return (new nodes\IntConstPattern($literal->value))->set('span', $pattern->span);
      case $literal instanceof ast\BoolLiteral:
        return (new nodes\BoolConstPattern($literal->value))->set('span', $pattern->span);
      default:
        die('unreachable');
    }
  }

  private static function wildcard_pattern(ast\WildcardPattern $pattern): nodes\WildcardPattern {
    return (new nodes\WildcardPattern())->set('span', $pattern->span);
  }

  private static function if_expr(ast\IfExpr $expr): nodes\IfExpr {
    $cond     = self::expr($expr->condition);
    $if_true  = self::block($expr->if_clause);
    $if_false = $expr->else_clause ? self::block($expr->else_clause) : null;
    return (new nodes\IfExpr($cond, $if_true, $if_false))->set('span', $expr->span);
  }

  private static function call_expr(ast\CallExpr $expr): nodes\CallExpr {
    $callee = self::expr($expr->callee);
    $args   = [];
    foreach ($expr->args as $arg) {
      $args[] = self::expr($arg);
    }
    return (new nodes\CallExpr($callee, $args))->set('span', $expr->span);
  }

  private static function variant_constructor_expr(ast\VariantConstructorExpr $expr): nodes\VariantConstructorExpr {
    $ref = self::path($expr->path);
    if ($expr->fields instanceof ast\NamedVariantConstructorFields) {
      $pairs = [];
      foreach ($expr->fields->pairs as $field) {
        $pairs[] = self::field_expr_node($field);
      }
      $fields = (new nodes\NamedVariantConstructorFields($pairs))->set('span', $expr->fields->span);
    } else if ($expr->fields instanceof ast\OrderedVariantConstructorFields) {
      $order = [];
      foreach ($expr->fields->order as $sub_expr) {
        $order[] = self::expr($sub_expr);
      }
      $fields = (new nodes\OrderedVariantConstructorFields($order))->set('span', $expr->fields->span);
    } else {
      $fields = null;
    }

    return (new nodes\VariantConstructorExpr($ref, $fields))->set('span', $expr->span);
  }

  private static function field_expr_node(ast\FieldExprNode $node): nodes\FieldExprNode {
    $name = (new nodes\Name($node->name->ident))->set('span', $node->name->span);
    $expr = self::expr($node->expr);
    return (new nodes\FieldExprNode($name, $expr))->set('span', $node->span);
  }

  private static function binary_expr(ast\BinaryExpr $expr): nodes\BinaryExpr {
    $op    = $expr->operator;
    $left  = self::expr($expr->left);
    $right = self::expr($expr->right);
    return (new nodes\BinaryExpr($op, $left, $right))->set('span', $expr->span);
  }

  private static function unary_expr(ast\UnaryExpr $expr): nodes\UnaryExpr {
    $op    = $expr->operator;
    $right = self::expr($expr->operand);
    return (new nodes\UnaryExpr($op, $right))->set('span', $expr->span);
  }

  private static function list_expr(ast\ListExpr $expr): nodes\ListExpr {
    $elements = [];
    foreach ($expr->elements as $element) {
      $elements[] = self::expr($element);
    }
    return (new nodes\ListExpr($elements))->set('span', $expr->span);
  }

  private static function path_expr(ast\PathExpr $expr): nodes\RefExpr {
    $ref = self::path($expr->path);
    return (new nodes\RefExpr($ref))->set('span', $expr->span);
  }

  private static function literal(ast\Literal $literal): nodes\Literal {
    switch (true) {
      case $literal instanceof ast\StrLiteral:
        return self::str_literal($literal);
      case $literal instanceof ast\FloatLiteral:
        return self::float_literal($literal);
      case $literal instanceof ast\IntLiteral:
        return self::int_literal($literal);
      case $literal instanceof ast\BoolLiteral:
        return self::bool_literal($literal);
      default:
        die('unreachable');
    }
  }

  private static function str_literal(ast\StrLiteral $expr): nodes\StrLiteral {
    $value = $expr->value;
    return (new nodes\StrLiteral($value))->set('span', $expr->span);
  }

  private static function float_literal(ast\FloatLiteral $expr): nodes\FloatLiteral {
    $value = $expr->value;
    return (new nodes\FloatLiteral($value, $expr->precision))->set('span', $expr->span);
  }

  private static function int_literal(ast\IntLiteral $expr): nodes\IntLiteral {
    $value = $expr->value;
    return (new nodes\IntLiteral($value))->set('span', $expr->span);
  }

  private static function bool_literal(ast\BoolLiteral $expr): nodes\BoolLiteral {
    $value = $expr->value;
    return (new nodes\BoolLiteral($value))->set('span', $expr->span);
  }

  private static function note(ast\Annotation $note): nodes\Note {
    switch (true) {
      case $note instanceof ast\TypeParamAnnotation:
        return self::param_note($note);
      case $note instanceof ast\UnitAnnotation:
        return self::unit_note($note);
      case $note instanceof ast\NamedAnnotation:
        return self::name_note($note);
      case $note instanceof ast\FunctionAnnotation:
        return self::func_note($note);
      case $note instanceof ast\ListAnnotation:
        return self::list_note($note);
      case $note instanceof ast\ParameterizedAnnotation:
        return self::parameterized_note($note);
      default:
        die('unreachable');
    }
  }

  private static function param_note(ast\TypeParamAnnotation $note): nodes\ParamNote {
    $name = (new nodes\Name($note->name))->set('span', $note->span); // TODO: this span should not include the left quote
    return (new nodes\ParamNote($name))->set('span', $note->span);
  }

  private static function unit_note(ast\UnitAnnotation $note): nodes\UnitNote {
    return (new nodes\UnitNote())->set('span', $note->span);
  }

  private static function name_note(ast\NamedAnnotation $note): nodes\NameNote {
    $ref = self::path($note->path);
    return (new nodes\NameNote($ref))->set('span', $note->span);
  }

  private static function func_note(ast\FunctionAnnotation $note): nodes\FuncNote {
    $inputs = [];
    foreach ($note->inputs as $input) {
      $inputs[] = self::note($input);
    }
    $output = self::note($note->output);
    return (new nodes\FuncNote($inputs, $output))->set('span', $note->span);
  }

  private static function list_note(ast\ListAnnotation $note): nodes\ListNote {
    if ($note->elements) {
      $elements = self::note($note->elements);
    } else {
      $elements = null;
    }
    return (new nodes\ListNote($elements))->set('span', $note->span);
  }

  private static function parameterized_note(ast\ParameterizedAnnotation $note): nodes\ParameterizedNote {
    $inner  = self::note($note->inner);
    $params = [];
    foreach ($note->params as $param) {
      $params[] = self::note($param);
    }
    return (new nodes\ParameterizedNote($inner, $params))->set('span', $note->span);
  }

  /**
   * Lower miscellaneous nodes
   */

  /**
   * @param ast\Attribute[] $attrs
   * @return array
   */
  private static function attrs(array $attrs): array {
    $hash = [];
    foreach ($attrs as $attr) {
      $hash[$attr->name] = true;
    }
    return $hash;
  }

  private static function func_params(array $params): array {
    $_params = [];
    foreach ($params as $param) {
      $name      = (new nodes\Name($param->name->ident))->set('span', $param->name->span);
      $note      = self::note($param->note);
      $_params[] = (new nodes\FuncParam($name, $note))->set('span', $param->span);
    }
    return $_params;
  }

  private static function block(ast\BlockNode $block): nodes\Block {
    $stmts = [];
    foreach ($block->stmts as $stmt) {
      $stmts[] = self::stmt($stmt);
    }
    return (new nodes\Block($stmts))->set('span', $block->span);
  }

  private static function compound_path(ast\CompoundPathNode $path): nodes\CompoundRef {
    $body = [];
    foreach ($path->body as $segment) {
      $body[] = (new nodes\Name($segment->ident))->set('span', $segment->span);
    }

    if ($path->tail instanceof ast\StarSegment) {
      $tail = (new nodes\StarRef())->set('span', $path->tail->span);
    } else {
      $tail = (new nodes\Name($path->tail->ident))->set('span', $path->tail->span);
    }

    return (new nodes\CompoundRef($path->extern, $body, $tail))->set('span', $path->span);
  }

  private static function path(ast\PathNode $path): nodes\Ref {
    $head = [];
    foreach ($path->head as $segment) {
      $head[] = (new nodes\Name($segment->ident))->set('span', $segment->span);
    }
    $tail = (new nodes\Name($path->tail->ident))->set('span', $path->tail->span);
    return (new nodes\Ref($path->extern, $head, $tail))->set('span', $path->span);
  }
}
