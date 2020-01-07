<?php

namespace Cthulhu\ir;

use Cthulhu\ast;
use Cthulhu\err\Error;
use Cthulhu\loc\Span;

class Lower {
  /**
   * @param ast\nodes\File $file
   * @return nodes\Library
   * @throws Error
   */
  public static function file(ast\nodes\File $file): nodes\Library {
    $name  = new nodes\Name($file->span->from->file->basename());
    $items = self::items($file->items);
    return (new nodes\Library($name, $items))->set('span', $file->span);
  }

  /**
   * @param ast\nodes\Item[] $items
   * @return nodes\Item[]
   * @throws Error
   */
  private static function items(array $items): array {
    $_items = [];
    foreach ($items as $item) {
      $_items[] = self::item($item);
    }
    return $_items;
  }

  /**
   * @param ast\nodes\Item $item
   * @return nodes\Item
   * @throws Error
   */
  private static function item(ast\nodes\Item $item): nodes\Item {
    switch (true) {
      case $item instanceof ast\nodes\ModItem:
        return self::mod_item($item);
      case $item instanceof ast\nodes\UseItem:
        return self::use_item($item);
      case $item instanceof ast\nodes\FnItem:
        return self::func_item($item);
      case $item instanceof ast\nodes\NativeFuncItem:
        return self::native_item($item);
      case $item instanceof ast\nodes\NativeTypeItem:
        return self::native_type_item($item);
      case $item instanceof ast\nodes\UnionItem:
        return self::union_item($item);
      default:
        die('unreachable');
    }
  }

  /**
   * @param ast\nodes\ModItem $item
   * @return nodes\ModItem
   * @throws Error
   */
  private static function mod_item(ast\nodes\ModItem $item): nodes\ModItem {
    $attrs = self::attrs($item->attrs);
    $name  = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    $items = self::items($item->items);
    return (new nodes\ModItem($name, $items, $attrs))->set('span', $item->span);
  }

  private static function use_item(ast\nodes\UseItem $item): nodes\UseItem {
    $attrs = self::attrs($item->attrs);
    $ref   = self::compound_path($item->path);
    return (new nodes\UseItem($ref, $attrs))->set('span', $item->span);
  }

  /**
   * @param ast\nodes\FnItem $item
   * @return nodes\FuncItem
   * @throws Error
   */
  private static function func_item(ast\nodes\FnItem $item): nodes\FuncItem {
    $attrs  = self::attrs($item->attrs);
    $name   = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    $params = self::func_params($item->params);
    $output = self::note($item->returns);
    $body   = self::block($item->body);
    $head   = (new nodes\FuncHead($name, $params, $output))->set('span', Span::join($item, $item->returns));
    return (new nodes\FuncItem($head, $body, $attrs))->set('span', $item->span);
  }

  private static function native_item(ast\nodes\NativeFuncItem $item): nodes\NativeFuncItem {
    $attrs = self::attrs($item->attrs);
    $name  = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    $note  = self::func_note($item->note);
    return (new nodes\NativeFuncItem($name, $note, $attrs))->set('span', $item->span);
  }

  private static function native_type_item(ast\nodes\NativeTypeItem $item): nodes\NativeTypeItem {
    $attrs = self::attrs($item->attrs);
    $name  = (new nodes\Name($item->name->ident))->set('span', $item->name->span);
    return (new nodes\NativeTypeItem($name, $attrs))->set('span', $item->span);
  }

  private static function union_item(ast\nodes\UnionItem $item): nodes\UnionItem {
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
   * @param ast\nodes\VariantDeclNode[] $variants
   * @return nodes\VariantDeclNode[]
   */
  private static function union_variants(array $variants): array {
    return array_map(function ($variant) {
      return self::union_variant_decl($variant);
    }, $variants);
  }

  private static function union_variant_decl(ast\nodes\VariantDeclNode $variant): nodes\VariantDeclNode {
    switch (true) {
      case $variant instanceof ast\nodes\NamedVariantDeclNode:
        return self::named_variant_decl($variant);
      case $variant instanceof ast\nodes\OrderedVariantDeclNode:
        return self::ordered_variant_decl($variant);
      case $variant instanceof ast\nodes\UnitVariantDeclNode:
        return self::unit_variant_decl($variant);
      default:
        die('unreachable');
    }
  }

  private static function named_variant_decl(ast\nodes\NamedVariantDeclNode $variant): nodes\NamedVariantDeclNode {
    $name   = (new nodes\Name($variant->name->ident))->set('span', $variant->name->span);
    $fields = [];
    foreach ($variant->fields as $field) {
      $field_name = (new nodes\Name($field->name->ident))->set('span', $field->name->span);
      $field_note = self::note($field->note);
      $fields[]   = (new nodes\FieldDeclNode($field_name, $field_note))->set('span', $field->span);
    }
    return (new nodes\NamedVariantDeclNode($name, $fields))->set('span', $variant->span);
  }

  private static function ordered_variant_decl(ast\nodes\OrderedVariantDeclNode $variant): nodes\OrderedVariantDeclNode {
    $name    = (new nodes\Name($variant->name->ident))->set('span', $variant->name->span);
    $members = [];
    foreach ($variant->members as $member) {
      $members[] = self::note($member);
    }
    return (new nodes\OrderedVariantDeclNode($name, $members))->set('span', $variant->span);
  }

  private static function unit_variant_decl(ast\nodes\UnitVariantDeclNode $variant): nodes\UnitVariantDeclNode {
    $name = (new nodes\Name($variant->name->ident))->set('span', $variant->name->span);
    return (new nodes\UnitVariantDeclNode($name))->set('span', $variant->span);
  }

  /**
   * @param ast\nodes\Stmt $stmt
   * @return nodes\Stmt
   * @throws Error
   */
  private static function stmt(ast\nodes\Stmt $stmt): nodes\Stmt {
    switch (true) {
      case $stmt instanceof ast\nodes\LetStmt:
        return self::let_stmt($stmt);
      case $stmt instanceof ast\nodes\SemiStmt:
        return self::semi_stmt($stmt);
      case $stmt instanceof ast\nodes\ExprStmt:
        return self::expr_stmt($stmt);
      default:
        die('unreachable');
    }
  }

  /**
   * @param ast\nodes\LetStmt $stmt
   * @return nodes\LetStmt
   * @throws Error
   */
  private static function let_stmt(ast\nodes\LetStmt $stmt): nodes\LetStmt {
    $name = (new nodes\Name($stmt->name->ident))->set('span', $stmt->name->span);
    $note = $stmt->note ? self::note($stmt->note) : null;
    $expr = self::expr($stmt->expr);
    return (new nodes\LetStmt($name, $note, $expr))->set('span', $stmt->span);
  }

  /**
   * @param ast\nodes\SemiStmt $stmt
   * @return nodes\SemiStmt
   * @throws Error
   */
  private static function semi_stmt(ast\nodes\SemiStmt $stmt): nodes\SemiStmt {
    $expr = self::expr($stmt->expr);
    return (new nodes\SemiStmt($expr))->set('span', $stmt->span);
  }

  /**
   * @param ast\nodes\ExprStmt $stmt
   * @return nodes\ReturnStmt
   * @throws Error
   */
  private static function expr_stmt(ast\nodes\ExprStmt $stmt): nodes\ReturnStmt {
    $expr = self::expr($stmt->expr);
    return (new nodes\ReturnStmt($expr))->set('span', $stmt->span);
  }

  /**
   * @param ast\nodes\Expr $expr
   * @return nodes\Expr
   * @throws Error
   */
  private static function expr(ast\nodes\Expr $expr): nodes\Expr {
    switch (true) {
      case $expr instanceof ast\nodes\MatchExpr:
        return self::match_expr($expr);
      case $expr instanceof ast\nodes\IfExpr:
        return self::if_expr($expr);
      case $expr instanceof ast\nodes\CallExpr:
        return self::call_expr($expr);
      case $expr instanceof ast\nodes\PipeExpr:
        return self::pipe_expr($expr);
      case $expr instanceof ast\nodes\VariantConstructorExpr:
        return self::variant_constructor_expr($expr);
      case $expr instanceof ast\nodes\BinaryExpr:
        return self::binary_expr($expr);
      case $expr instanceof ast\nodes\UnaryExpr:
        return self::unary_expr($expr);
      case $expr instanceof ast\nodes\ListExpr:
        return self::list_expr($expr);
      case $expr instanceof ast\nodes\PathExpr:
        return self::path_expr($expr);
      case $expr instanceof ast\nodes\Literal:
        return self::literal($expr);
      case $expr instanceof ast\nodes\UnitLiteral:
        return self::unit($expr);
      default:
        die('unreachable');
    }
  }

  /**
   * @param ast\nodes\MatchExpr $expr
   * @return nodes\MatchExpr
   * @throws Error
   */
  private static function match_expr(ast\nodes\MatchExpr $expr): nodes\MatchExpr {
    $disc = (new nodes\MatchDiscriminant(self::expr($expr->disc)))->set('span', $expr->disc->span);
    $arms = [];
    foreach ($expr->arms as $arm) {
      $arms[] = self::match_arm($arm);
    }
    return (new nodes\MatchExpr($disc, $arms))->set('span', $expr->span);
  }

  /**
   * @param ast\nodes\MatchArm $arm
   * @return nodes\MatchArm
   * @throws Error
   */
  private static function match_arm(ast\nodes\MatchArm $arm): nodes\MatchArm {
    $pattern = self::pattern($arm->pattern);
    $handler = (new nodes\MatchHandler(new nodes\ReturnStmt(self::expr($arm->handler))))->set('span', $arm->handler->span);
    return (new nodes\MatchArm($pattern, $handler))->set('span', $arm->span);
  }

  /**
   * @param ast\nodes\Pattern $pattern
   * @return nodes\Pattern
   * @throws Error
   */
  private static function pattern(ast\nodes\Pattern $pattern): nodes\Pattern {
    switch (true) {
      case $pattern instanceof ast\nodes\VariantPattern:
        return self::variant_pattern($pattern);
      case $pattern instanceof ast\nodes\VariablePattern:
        return self::variable_pattern($pattern);
      case $pattern instanceof ast\nodes\ConstPattern:
        return self::const_pattern($pattern);
      case $pattern instanceof ast\nodes\WildcardPattern:
        return self::wildcard_pattern($pattern);
      default:
        die('unreachable');
    }
  }

  /**
   * @param ast\nodes\VariantPattern $pattern
   * @return nodes\VariantPattern
   * @throws Error
   */
  private static function variant_pattern(ast\nodes\VariantPattern $pattern): nodes\VariantPattern {
    $ref    = self::path($pattern->path);
    $fields = self::variant_pattern_fields($pattern->fields);
    return (new nodes\VariantPattern($ref, $fields))->set('span', $pattern->span);
  }

  /**
   * @param ast\nodes\VariantPatternFields|null $fields
   * @return nodes\VariantPatternFields|null
   * @throws Error
   */
  private static function variant_pattern_fields(?ast\nodes\VariantPatternFields $fields): ?nodes\VariantPatternFields {
    switch (true) {
      case $fields instanceof ast\nodes\NamedVariantPatternFields:
        return self::named_variant_pattern_fields($fields);
      case $fields instanceof ast\nodes\OrderedVariantPatternFields:
        return self::ordered_variant_pattern_fields($fields);
      default:
        return null;
    }
  }

  /**
   * @param ast\nodes\NamedVariantPatternFields $fields
   * @return nodes\NamedVariantPatternFields
   * @throws Error
   */
  private static function named_variant_pattern_fields(ast\nodes\NamedVariantPatternFields $fields): nodes\NamedVariantPatternFields {
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

  /**
   * @param ast\nodes\NamedPatternField $field
   * @return nodes\NamedPatternField
   * @throws Error
   */
  private static function named_pattern_field(ast\nodes\NamedPatternField $field): nodes\NamedPatternField {
    $name    = (new nodes\Name($field->name->ident))->set('span', $field->name->span);
    $pattern = self::pattern($field->pattern);
    return (new nodes\NamedPatternField($name, $pattern))->set('span', $field->span);
  }

  /**
   * @param ast\nodes\OrderedVariantPatternFields $fields
   * @return nodes\OrderedVariantPatternFields
   * @throws Error
   */
  private static function ordered_variant_pattern_fields(ast\nodes\OrderedVariantPatternFields $fields): nodes\OrderedVariantPatternFields {
    $order = [];
    foreach ($fields->order as $index => $pattern) {
      $order[$index] = (new nodes\OrderedVariantPatternField($index, self::pattern($pattern)))->set('span', $pattern->span);
    }
    return (new nodes\OrderedVariantPatternFields($order))->set('span', $fields->span);
  }

  private static function variable_pattern(ast\nodes\VariablePattern $pattern): nodes\VariablePattern {
    $name = (new nodes\Name($pattern->name->ident))->set('span', $pattern->name->span);
    return (new nodes\VariablePattern($name))->set('span', $pattern->span);
  }

  private static function const_pattern(ast\nodes\ConstPattern $pattern): nodes\ConstPattern {
    $literal = $pattern->literal;
    switch (true) {
      case $literal instanceof ast\nodes\StrLiteral:
        return (new nodes\StrConstPattern($literal->value))->set('span', $pattern->span);
      case $literal instanceof ast\nodes\FloatLiteral:
        return (new nodes\FloatConstPattern($literal->value))->set('span', $pattern->span);
      case $literal instanceof ast\nodes\IntLiteral:
        return (new nodes\IntConstPattern($literal->value))->set('span', $pattern->span);
      case $literal instanceof ast\nodes\BoolLiteral:
        return (new nodes\BoolConstPattern($literal->value))->set('span', $pattern->span);
      default:
        die('unreachable');
    }
  }

  private static function wildcard_pattern(ast\nodes\WildcardPattern $pattern): nodes\WildcardPattern {
    return (new nodes\WildcardPattern())->set('span', $pattern->span);
  }

  /**
   * @param ast\nodes\IfExpr $expr
   * @return nodes\IfExpr
   * @throws Error
   */
  private static function if_expr(ast\nodes\IfExpr $expr): nodes\IfExpr {
    $cond     = self::expr($expr->condition);
    $if_true  = self::block($expr->if_clause);
    $if_false = $expr->else_clause ? self::block($expr->else_clause) : null;
    return (new nodes\IfExpr($cond, $if_true, $if_false))->set('span', $expr->span);
  }

  /**
   * @param ast\nodes\CallExpr $expr
   * @return nodes\CallExpr
   * @throws Error
   */
  private static function call_expr(ast\nodes\CallExpr $expr): nodes\CallExpr {
    $callee = self::expr($expr->callee);

    if ($callee instanceof nodes\CallExpr) {
      $args = $callee->args;
      foreach ($expr->args as $arg) {
        $args[] = self::expr($arg);
      }
      return (new nodes\CallExpr($callee->callee, $args))->set('span', $expr->span);
    }

    $args = [];
    foreach ($expr->args as $arg) {
      $args[] = self::expr($arg);
    }
    if (empty($expr->args)) {
      $args[] = (new nodes\UnitLiteral())->set('span', $expr->span);
    }
    return (new nodes\CallExpr($callee, $args))->set('span', $expr->span);
  }

  /**
   * @param ast\nodes\PipeExpr $expr
   * @return nodes\PipeExpr
   * @throws Error
   */
  private static function pipe_expr(ast\nodes\PipeExpr $expr): nodes\PipeExpr {
    $left  = self::expr($expr->left);
    $right = self::expr($expr->right);
    return (new nodes\PipeExpr($left, $right))->set('span', $expr->span);
  }

  /**
   * @param ast\nodes\VariantConstructorExpr $expr
   * @return nodes\VariantConstructorExpr
   * @throws Error
   */
  private static function variant_constructor_expr(ast\nodes\VariantConstructorExpr $expr): nodes\VariantConstructorExpr {
    $ref = self::path($expr->path);
    if ($expr->fields instanceof ast\nodes\NamedVariantConstructorFields) {
      $pairs = [];
      foreach ($expr->fields->pairs as $field) {
        $pairs[] = self::field_expr_node($field);
      }
      $fields = (new nodes\NamedVariantConstructorFields($pairs))->set('span', $expr->fields->span);
    } else if ($expr->fields instanceof ast\nodes\OrderedVariantConstructorFields) {
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

  /**
   * @param ast\nodes\FieldExprNode $node
   * @return nodes\FieldExprNode
   * @throws Error
   */
  private static function field_expr_node(ast\nodes\FieldExprNode $node): nodes\FieldExprNode {
    $name = (new nodes\Name($node->name->ident))->set('span', $node->name->span);
    $expr = self::expr($node->expr);
    return (new nodes\FieldExprNode($name, $expr))->set('span', $node->span);
  }

  /**
   * @param ast\nodes\BinaryExpr $expr
   * @return nodes\BinaryExpr
   * @throws Error
   */
  private static function binary_expr(ast\nodes\BinaryExpr $expr): nodes\BinaryExpr {
    $op    = $expr->operator;
    $left  = self::expr($expr->left);
    $right = self::expr($expr->right);
    return (new nodes\BinaryExpr($op, $left, $right))->set('span', $expr->span);
  }

  /**
   * @param ast\nodes\UnaryExpr $expr
   * @return nodes\UnaryExpr
   * @throws Error
   */
  private static function unary_expr(ast\nodes\UnaryExpr $expr): nodes\UnaryExpr {
    $op    = $expr->operator;
    $right = self::expr($expr->operand);
    return (new nodes\UnaryExpr($op, $right))->set('span', $expr->span);
  }

  /**
   * @param ast\nodes\ListExpr $expr
   * @return nodes\ListExpr
   * @throws Error
   */
  private static function list_expr(ast\nodes\ListExpr $expr): nodes\ListExpr {
    $elements = [];
    foreach ($expr->elements as $element) {
      $elements[] = self::expr($element);
    }
    return (new nodes\ListExpr($elements))->set('span', $expr->span);
  }

  private static function path_expr(ast\nodes\PathExpr $expr): nodes\RefExpr {
    $ref = self::path($expr->path);
    return (new nodes\RefExpr($ref))->set('span', $expr->span);
  }

  private static function literal(ast\nodes\Literal $literal): nodes\Literal {
    switch (true) {
      case $literal instanceof ast\nodes\StrLiteral:
        return self::str_literal($literal);
      case $literal instanceof ast\nodes\FloatLiteral:
        return self::float_literal($literal);
      case $literal instanceof ast\nodes\IntLiteral:
        return self::int_literal($literal);
      case $literal instanceof ast\nodes\BoolLiteral:
        return self::bool_literal($literal);
      default:
        die('unreachable');
    }
  }

  private static function str_literal(ast\nodes\StrLiteral $expr): nodes\StrLiteral {
    $value = $expr->value;
    return (new nodes\StrLiteral($value))->set('span', $expr->span);
  }

  private static function float_literal(ast\nodes\FloatLiteral $expr): nodes\FloatLiteral {
    $value = $expr->value;
    return (new nodes\FloatLiteral($value, $expr->precision))->set('span', $expr->span);
  }

  private static function int_literal(ast\nodes\IntLiteral $expr): nodes\IntLiteral {
    $value = $expr->value;
    return (new nodes\IntLiteral($value))->set('span', $expr->span);
  }

  private static function bool_literal(ast\nodes\BoolLiteral $expr): nodes\BoolLiteral {
    $value = $expr->value;
    return (new nodes\BoolLiteral($value))->set('span', $expr->span);
  }

  private static function unit(ast\nodes\UnitLiteral $expr): nodes\UnitLiteral {
    return (new nodes\UnitLiteral())->set('span', $expr->span);
  }

  private static function note(ast\nodes\Annotation $note): nodes\Note {
    switch (true) {
      case $note instanceof ast\nodes\TypeParamAnnotation:
        return self::param_note($note);
      case $note instanceof ast\nodes\UnitAnnotation:
        return self::unit_note($note);
      case $note instanceof ast\nodes\NamedAnnotation:
        return self::name_note($note);
      case $note instanceof ast\nodes\FunctionAnnotation:
        return self::func_note($note);
      case $note instanceof ast\nodes\ListAnnotation:
        return self::list_note($note);
      case $note instanceof ast\nodes\ParameterizedAnnotation:
        return self::parameterized_note($note);
      default:
        die('unreachable');
    }
  }

  private static function param_note(ast\nodes\TypeParamAnnotation $note): nodes\ParamNote {
    $name = (new nodes\Name($note->name))->set('span', $note->span); // TODO: this span should not include the left quote
    return (new nodes\ParamNote($name))->set('span', $note->span);
  }

  private static function unit_note(ast\nodes\UnitAnnotation $note): nodes\UnitNote {
    return (new nodes\UnitNote())->set('span', $note->span);
  }

  private static function name_note(ast\nodes\NamedAnnotation $note): nodes\NameNote {
    $ref = self::path($note->path);
    return (new nodes\NameNote($ref))->set('span', $note->span);
  }

  private static function func_note(ast\nodes\FunctionAnnotation $note): nodes\FuncNote {
    $inputs = [];
    foreach ($note->inputs as $input) {
      $inputs[] = self::note($input);
    }
    $output = self::note($note->output);
    return (new nodes\FuncNote($inputs, $output))->set('span', $note->span);
  }

  private static function list_note(ast\nodes\ListAnnotation $note): nodes\ListNote {
    if ($note->elements) {
      $elements = self::note($note->elements);
    } else {
      $elements = null;
    }
    return (new nodes\ListNote($elements))->set('span', $note->span);
  }

  private static function parameterized_note(ast\nodes\ParameterizedAnnotation $note): nodes\ParameterizedNote {
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
   * @param ast\nodes\Attribute[] $attrs
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

  /**
   * @param ast\nodes\BlockNode $block
   * @return nodes\Block
   * @throws Error
   */
  private static function block(ast\nodes\BlockNode $block): nodes\Block {
    $stmts = [];
    foreach ($block->stmts as $stmt) {
      $stmts[] = self::stmt($stmt);
    }

    if (empty($stmts) || (end($stmts) instanceof nodes\ReturnStmt) === false) {
      $span    = $block->span->to->prev_column()->to_span();
      $unit    = (new nodes\UnitLiteral())->set('span', $span);
      $stmts[] = (new nodes\ReturnStmt($unit))->set('span', $span);
    }

    return (new nodes\Block($stmts))->set('span', $block->span);
  }

  private static function compound_path(ast\nodes\CompoundPathNode $path): nodes\CompoundRef {
    $body = [];
    foreach ($path->body as $segment) {
      $body[] = (new nodes\Name($segment->ident))->set('span', $segment->span);
    }

    if ($path->tail instanceof ast\nodes\StarSegment) {
      $tail = (new nodes\StarRef())->set('span', $path->tail->span);
    } else {
      $tail = (new nodes\Name($path->tail->ident))->set('span', $path->tail->span);
    }

    return (new nodes\CompoundRef($path->extern, $body, $tail))->set('span', $path->span);
  }

  private static function path(ast\nodes\PathNode $path): nodes\Ref {
    $head = [];
    foreach ($path->head as $segment) {
      $head[] = (new nodes\Name($segment->ident))->set('span', $segment->span);
    }
    $tail = (new nodes\Name($path->tail->ident))->set('span', $path->tail->span);
    return (new nodes\Ref($path->extern, $head, $tail))->set('span', $path->span);
  }
}
