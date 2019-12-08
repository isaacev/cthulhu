<?php

namespace Cthulhu\Parser;

use Cthulhu\ast;
use Cthulhu\Errors\Error;
use Cthulhu\Parser\Lexer\Lexer;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;
use Cthulhu\Source;

class Parser {
  /**
   * @param Source\File $file
   * @return ast\File
   * @throws Error
   */
  public static function file_to_ast(Source\File $file): ast\File {
    $lexer  = Lexer::from_file($file);
    $parser = new self($file, $lexer);
    return $parser->file();
  }

  private Source\File $file;
  private Lexer $lexer;

  function __construct(Source\File $file, Lexer $lexer) {
    $this->file  = $file;
    $this->lexer = $lexer;
  }

  /**
   * @return ast\File
   * @throws Error
   */
  public function file(): ast\File {
    return new ast\File($this->file, $this->items(false));
  }

  /**
   * ITEMS
   *
   * Items are nodes that can only exist at the top-level of a file or as
   * children of a `mod { ... }` node. Items can import a module into the
   * current namespace, declare a function, or declare a submodule.
   */

  /**
   * @param bool $brace_wrapped
   * @return array
   * @throws Error
   */
  private function items(bool $brace_wrapped): array {
    $items = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek->type === TokenType::EOF) {
        break;
      } else if ($brace_wrapped && $peek->type === TokenType::BRACE_RIGHT) {
        break;
      }
      $items[] = $this->item();
    }
    return $items;
  }

  /**
   * @return ast\Item
   * @throws Error
   */
  private function item(): ast\Item {
    $attrs = $this->attributes();

    switch ($this->lexer->peek()->type) {
      case TokenType::KEYWORD_USE:
        return $this->use_item($attrs);
      case TokenType::KEYWORD_MOD:
        return $this->mod_item($attrs);
      case TokenType::KEYWORD_FN:
        return $this->fn_item($attrs);
      case TokenType::KEYWORD_NATIVE:
        return $this->native_item($attrs);
      case TokenType::KEYWORD_TYPE:
        return $this->union_item($attrs);
      default:
        throw Errors::expected_item($this->lexer->next());
    }
  }

  /**
   * @return array
   * @throws Error
   */
  private function attributes(): array {
    $attrs = [];
    while ($this->lexer->peek()->type === TokenType::POUND) {
      $attrs[] = $this->attribute();
    }
    return $attrs;
  }

  /**
   * @return ast\Attribute
   * @throws Error
   */
  private function attribute(): ast\Attribute {
    $pound = $this->next(TokenType::POUND);
    $this->next(TokenType::BRACKET_LEFT);
    $name          = $this->next(TokenType::LOWER_NAME)->lexeme;
    $bracket_right = $this->next(TokenType::BRACKET_RIGHT);
    $span          = $pound->span->extended_to($bracket_right->span);
    return new ast\Attribute($span, $name);
  }

  /**
   * @param array $attrs
   * @return ast\UseItem
   * @throws Error
   */
  private function use_item(array $attrs): ast\UseItem {
    $keyword = $this->next(TokenType::KEYWORD_USE);
    $path    = $this->compound_path_node();
    $semi    = $this->semicolon();
    $span    = $keyword->span->extended_to($semi->span);
    return new ast\UseItem($span, $path, $attrs);
  }

  /**
   * @param array $attrs
   * @return ast\ModItem
   * @throws Error
   */
  private function mod_item(array $attrs): ast\ModItem {
    $keyword = $this->next(TokenType::KEYWORD_MOD);
    $name    = ast\UpperNameNode::from_token($this->next(TokenType::UPPER_NAME));
    $this->next(TokenType::BRACE_LEFT);
    $items       = $this->items(true);
    $right_brace = $this->next(TokenType::BRACE_RIGHT);
    $span        = $keyword->span->extended_to($right_brace->span);
    return new ast\ModItem($span, $name, $items, $attrs);
  }

  /**
   * @param array $attrs
   * @return ast\Item
   * @throws Error
   */
  private function native_item(array $attrs): ast\Item {
    $native = $this->next(TokenType::KEYWORD_NATIVE);

    switch ($this->lexer->peek()->type) {
      case TokenType::KEYWORD_FN:
      {
        $this->next(TokenType::KEYWORD_FN);
        $name = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));
        $note = $this->function_annotation($this->grouped_annotation());
        $semi = $this->next(TokenType::SEMICOLON);
        $span = $native->span->extended_to($semi->span);
        return new ast\NativeFuncItem($span, $name, $note, $attrs);
      }
      default:
      {
        $this->next(TokenType::KEYWORD_TYPE);
        $name = ast\UpperNameNode::from_token($this->next(TokenType::UPPER_NAME));
        $semi = $this->next(TokenType::SEMICOLON);
        $span = $native->span->extended_to($semi->span);
        return new ast\NativeTypeItem($span, $name, $attrs);
      }
    }
  }

  /**
   * @param array $attrs
   * @return ast\UnionItem
   * @throws Error
   */
  private function union_item(array $attrs): ast\UnionItem {
    $type   = $this->next(TokenType::KEYWORD_TYPE);
    $name   = ast\UpperNameNode::from_token($this->next(TokenType::UPPER_NAME));
    $params = [];
    if ($this->lexer->peek()->type === TokenType::PAREN_LEFT) {
      $this->next(TokenType::PAREN_LEFT);
      $params[] = $this->type_param_annotation();
      while ($this->lexer->peek()->type === TokenType::COMMA) {
        $this->next(TokenType::COMMA);
        $params[] = $this->type_param_annotation();
      }
      $this->next(TokenType::PAREN_RIGHT);
    }
    $this->next(TokenType::EQUALS);
    $variants = [ $this->variant_node() ];
    while ($this->lexer->peek()->type === TokenType::PIPE) {
      $variants[] = $this->variant_node();
    }
    $semi = $this->next(TokenType::SEMICOLON);
    $span = $type->span->extended_to($semi->span);
    return new ast\UnionItem($span, $name, $params, $variants, $attrs);
  }

  /**
   * @return ast\VariantDeclNode
   * @throws Error
   */
  private function variant_node(): ast\VariantDeclNode {
    $this->next(TokenType::PIPE);
    $name = ast\UpperNameNode::from_token($this->next(TokenType::UPPER_NAME));
    switch ($this->lexer->peek()->type) {
      case TokenType::PAREN_LEFT:
        return $this->ordered_variant_decl_node($name);
      case TokenType::BRACE_LEFT:
        return $this->named_variant_decl_node($name);
      default:
        return $this->unit_variant_decl_node($name);
    }
  }

  /**
   * @param ast\UpperNameNode $name
   * @return ast\OrderedVariantDeclNode
   * @throws Error
   * @noinspection DuplicatedCode
   */
  private function ordered_variant_decl_node(ast\UpperNameNode $name): ast\OrderedVariantDeclNode {
    $this->next(TokenType::PAREN_LEFT);
    $members = [ $this->type_annotation() ];
    while ($this->lexer->peek()->type === TokenType::COMMA) {
      $this->next(TokenType::COMMA);
      $members[] = $this->type_annotation();
    }
    $paren_right = $this->next(TokenType::PAREN_RIGHT);
    $span        = $name->span->extended_to($paren_right->span);
    return new ast\OrderedVariantDeclNode($span, $name, $members);
  }

  /**
   * @param ast\UpperNameNode $name
   * @return ast\NamedVariantDeclNode
   * @throws Error
   */
  private function named_variant_decl_node(ast\UpperNameNode $name): ast\NamedVariantDeclNode {
    $this->next(TokenType::BRACE_LEFT);
    $fields = [ $this->field_decl() ];
    while ($this->lexer->peek()->type === TokenType::COMMA) {
      $this->next(TokenType::COMMA);
      $fields[] = $this->field_decl();
    }
    $brace_right = $this->next(TokenType::BRACE_RIGHT);
    $span        = $name->span->extended_to($brace_right->span);
    return new ast\NamedVariantDeclNode($span, $name, $fields);
  }

  /**
   * @param ast\UpperNameNode $name
   * @return ast\UnitVariantDeclNode
   */
  private function unit_variant_decl_node(ast\UpperNameNode $name): ast\UnitVariantDeclNode {
    return new ast\UnitVariantDeclNode($name);
  }

  /**
   * @return ast\FieldDeclNode
   * @throws Error
   */
  private function field_decl(): ast\FieldDeclNode {
    $name = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));
    $this->next(TokenType::COLON);
    $note = $this->type_annotation();
    $span = $name->span->extended_to($note->span);
    return new ast\FieldDeclNode($span, $name, $note);
  }

  /**
   * @return ast\FieldExprNode
   * @throws Error
   */
  private function field_expr(): ast\FieldExprNode {
    $name = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));
    $this->next(TokenType::COLON);
    $expr = $this->expr();
    $span = $name->span->extended_to($expr->span);
    return new ast\FieldExprNode($span, $name, $expr);
  }

  /**
   * @param array $attrs
   * @return ast\FnItem
   * @throws Error
   */
  private function fn_item(array $attrs): ast\FnItem {
    $fn_keyword = $this->next(TokenType::KEYWORD_FN);
    $fn_name    = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));

    $this->next(TokenType::PAREN_LEFT);

    $params = [];
    if ($this->lexer->peek()->type !== TokenType::PAREN_RIGHT) {
      while (true) {
        $param_name = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));
        $this->next(TokenType::COLON);
        $param_note = $this->type_annotation();
        $param_span = new Source\Span($param_name->span->from, $param_note->span->to);
        $params[]   = new ast\ParamNode($param_span, $param_name, $param_note);

        if ($this->lexer->peek()->type === TokenType::COMMA) {
          $this->next(TokenType::COMMA);
          continue;
        } else {
          break;
        }
      }
    }

    $this->next(TokenType::PAREN_RIGHT);
    $this->next(TokenType::THIN_ARROW);
    $returns = $this->type_annotation();
    $body    = $this->block();
    $fn_span = $fn_keyword->span->extended_to($body->span);
    return new ast\FnItem($fn_span, $fn_name, $params, $returns, $body, $attrs);
  }

  /**
   * STATEMENTS
   *
   * Statements can only exist as the children of a `Block` node. Block nodes
   * are children of either a function declaration or an if/else conditional.
   */

  /**
   * @return ast\BlockNode
   * @throws Error
   */
  private function block(): ast\BlockNode {
    $left  = $this->next(TokenType::BRACE_LEFT);
    $stmts = $this->stmts();
    $right = $this->next(TokenType::BRACE_RIGHT);
    $span  = $left->span->extended_to($right->span);
    return new ast\BlockNode($span, $stmts);
  }

  /**
   * @return array
   * @throws Error
   */
  private function stmts(): array {
    $stmts = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek->type === TokenType::BRACE_RIGHT) {
        break;
      }
      if (($stmts[] = $this->stmt()) instanceof ast\ExprStmt) {
        break;
      }
    }
    return $stmts;
  }

  /**
   * @return ast\Stmt
   * @throws Error
   */
  private function stmt(): ast\Stmt {
    $attrs = $this->attributes();

    switch ($this->lexer->peek()->type) {
      case TokenType::KEYWORD_LET:
        return $this->let_stmt($attrs);
      default:
        return $this->expr_stmt($attrs);
    }
  }

  /**
   * @param array $attrs
   * @return ast\LetStmt
   * @throws Error
   */
  private function let_stmt(array $attrs): ast\LetStmt {
    $keyword = $this->next(TokenType::KEYWORD_LET);
    $name    = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));

    if ($this->lexer->peek()->type === TokenType::COLON) {
      $this->next(TokenType::COLON);
      $annotation = $this->type_annotation();
    } else {
      $annotation = null;
    }

    $this->next(TokenType::EQUALS);
    $expr = $this->expr();
    $semi = $this->semicolon();
    $span = $keyword->span->extended_to($semi->span);
    return new ast\LetStmt($span, $name, $annotation, $expr, $attrs);
  }

  /**
   * @param array $attrs
   * @return ast\Stmt
   * @throws Error
   */
  private function expr_stmt(array $attrs): ast\Stmt {
    $expr = $this->expr();
    if ($this->lexer->peek()->type === TokenType::SEMICOLON) {
      $semi = $this->semicolon();
      return new ast\SemiStmt($expr, $semi, $attrs);
    } else {
      return new ast\ExprStmt($expr, $attrs);
    }
  }

  /**
   * EXPRESSIONS
   *
   * Expressions always produce a value and return it to the parent node.
   */

  /**
   * @param int $threshold
   * @return ast\Expr
   * @throws Error
   */
  private function expr(int $threshold = Precedence::LOWEST): ast\Expr {
    $left = $this->prefix_expr();
    while ($threshold < $this->infix_token_precedence($this->lexer->peek())) {
      $left = $this->postfix_expr($left, $this->lexer->next());
    }
    return $left;
  }

  /**
   * @return ast\Expr
   * @throws Error
   */
  private function prefix_expr(): ast\Expr {
    $peek = $this->lexer->peek();
    switch ($peek->type) {
      case TokenType::KEYWORD_MATCH:
        return $this->match_expr($this->lexer->next());
      case TokenType::KEYWORD_IF:
        return $this->if_expr($this->lexer->next());
      case TokenType::DASH:
        return $this->unary_prefix_expr($this->lexer->next());
      case TokenType::BRACKET_LEFT:
        return $this->list_expr($this->lexer->next());
      case TokenType::PAREN_LEFT:
        $this->lexer->next();
        return $this->group_expr();
      case TokenType::UPPER_NAME:
      case TokenType::LOWER_NAME:
      case TokenType::DOUBLE_COLON:
        return $this->path_expr();
      case TokenType::LITERAL_STR:
        return $this->str_literal($this->lexer->next());
      case TokenType::LITERAL_FLOAT:
        return $this->float_literal($this->lexer->next());
      case TokenType::LITERAL_INT:
        return $this->int_literal($this->lexer->next());
      case TokenType::LITERAL_BOOL:
        return $this->bool_literal($this->lexer->next());
      default:
        $next = $this->lexer->next();
        throw Errors::exepcted_expression($next);
    }
  }

  /**
   * @param ast\Expr $left
   * @param Token    $next
   * @return ast\Expr
   * @throws Error
   */
  private function postfix_expr(ast\Expr $left, Token $next): ast\Expr {
    switch ($next->type) {
      case TokenType::PAREN_LEFT:
      case TokenType::BRACKET_LEFT:
        return $this->call_expr($left);
      case TokenType::PLUS:
      case TokenType::PLUS_PLUS:
      case TokenType::DASH:
      case TokenType::STAR:
      case TokenType::SLASH:
      case TokenType::LESS_THAN:
      case TokenType::LESS_THAN_EQ:
      case TokenType::GREATER_THAN:
      case TokenType::GREATER_THAN_EQ:
      case TokenType::CARET:
      case TokenType::DOUBLE_EQUALS:
        return $this->binary_infix_expr($left, $next);
      default:
        // This condition *should* be unreachable unless there's a bug in the
        // parser where the `Parser::infix_token_precedence` method thinks a
        // token is a binary operator but this method doesn't recognize that
        // token as an operator.
        die('binary operator disagreement: ' . $next->type);
    }
  }

  /**
   * @param ast\Expr $left
   * @param Token    $operator
   * @return ast\BinaryExpr
   * @throws Error
   */
  private function binary_infix_expr(ast\Expr $left, Token $operator): ast\BinaryExpr {
    $right = $this->expr($this->infix_token_precedence($this->lexer->peek()));
    $span  = $left->span->extended_to($right->span);
    return new ast\BinaryExpr($span, $operator->lexeme, $left, $right);
  }

  /**
   * @param Token $operator
   * @return ast\UnaryExpr
   * @throws Error
   */
  private function unary_prefix_expr(Token $operator): ast\UnaryExpr {
    $operand = self::expr(Precedence::UNARY);
    $span    = $operator->span->extended_to($operand->span);
    return new ast\UnaryExpr($span, $operator->lexeme, $operand);
  }

  /**
   * @param Token $match_keyword
   * @return ast\MatchExpr
   * @throws Error
   */
  private function match_expr(Token $match_keyword): ast\MatchExpr {
    $disc = $this->expr();
    $this->next(TokenType::BRACE_LEFT);

    $arms = [ $this->match_arm() ];
    while ($this->lexer->peek()->type !== TokenType::BRACE_RIGHT) {
      $arms[] = $this->match_arm();
    }

    $brace_right = $this->next(TokenType::BRACE_RIGHT);
    $span        = $match_keyword->span->extended_to($brace_right->span);
    return new ast\MatchExpr($span, $disc, $arms);
  }

  /**
   * @return ast\MatchArm
   * @throws Error
   */
  private function match_arm(): ast\MatchArm {
    $pattern = $this->match_pattern();
    $this->next(TokenType::FAT_ARROW);
    $handler = $this->expr();
    $comma   = $this->next(TokenType::COMMA);
    $span    = $pattern->span->extended_to($comma->span);
    return new ast\MatchArm($span, $pattern, $handler);
  }

  /**
   * @return ast\Pattern
   * @throws Error
   */
  private function match_pattern(): ast\Pattern {
    switch ($this->lexer->peek()->type) {
      case TokenType::UPPER_NAME:
        return $this->variant_pattern();
      case TokenType::LOWER_NAME:
        return $this->match_varible_pattern();
      case TokenType::LITERAL_STR:
        return $this->match_str_const_pattern();
      case TokenType::LITERAL_FLOAT:
        return $this->match_float_const_pattern();
      case TokenType::LITERAL_INT:
        return $this->match_int_const_pattern();
      case TokenType::LITERAL_BOOL:
        return $this->match_bool_const_pattern();
      default:
        return $this->match_wildcard_pattern();
    }
  }

  /**
   * @return ast\VariantPattern
   * @throws Error
   */
  private function variant_pattern(): ast\VariantPattern {
    $path = $this->path_node();
    assert($path->tail instanceof ast\UpperNameNode); // TODO: handle this with a proper error message
    switch ($this->lexer->peek()->type) {
      case TokenType::BRACE_LEFT:
        $fields = $this->named_variant_pattern_fields();
        break;
      case TokenType::PAREN_LEFT:
        $fields = $this->ordered_variant_pattern_fields();
        break;
      default:
        $fields = null;
    }

    $span = $fields ? $path->span->extended_to($fields->span) : $path->span;
    return new ast\VariantPattern($span, $path, $fields);
  }

  /**
   * @return ast\OrderedVariantPatternFields
   * @throws Error
   */
  private function ordered_variant_pattern_fields(): ast\OrderedVariantPatternFields {
    $paren_left = $this->next(TokenType::PAREN_LEFT);
    $order      = [];
    while ($this->lexer->peek()->type !== TokenType::PAREN_RIGHT) {
      $order[] = $this->match_pattern();
      if ($this->lexer->peek()->type === TokenType::COMMA) {
        $this->next(TokenType::COMMA);
        continue;
      } else {
        break;
      }
    }
    $paren_right = $this->next(TokenType::PAREN_RIGHT);
    $span        = $paren_left->span->extended_to($paren_right->span);
    return new ast\OrderedVariantPatternFields($span, $order);
  }

  /**
   * @return ast\NamedVariantPatternFields
   * @throws Error
   */
  private function named_variant_pattern_fields(): ast\NamedVariantPatternFields {
    $brace_left = $this->next(TokenType::BRACE_LEFT);
    $mapping    = [];
    while ($this->lexer->peek()->type !== TokenType::BRACE_RIGHT) {
      $name = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));
      $this->next(TokenType::COLON);
      $pattern   = $this->match_pattern();
      $span      = $name->span->extended_to($pattern->span);
      $mapping[] = new ast\NamedPatternField($span, $name, $pattern);
      if ($this->lexer->peek()->type === TokenType::COMMA) {
        $this->next(TokenType::COMMA);
        continue;
      } else {
        break;
      }
    }
    $brace_right = $this->next(TokenType::BRACE_RIGHT);
    $span        = $brace_left->span->extended_to($brace_right->span);
    return new ast\NamedVariantPatternFields($span, $mapping);
  }

  /**
   * @return ast\WildcardPattern
   * @throws Error
   */
  private function match_wildcard_pattern(): ast\WildcardPattern {
    $underscore = $this->next(TokenType::UNDERSCORE);
    return new ast\WildcardPattern($underscore->span);
  }

  /**
   * @return ast\VariablePattern
   * @throws Error
   */
  private function match_varible_pattern(): ast\VariablePattern {
    $name = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));
    return new ast\VariablePattern($name);
  }

  /**
   * @return ast\ConstPattern
   * @throws Error
   */
  private function match_str_const_pattern(): ast\ConstPattern {
    $literal = $this->str_literal($this->next(TokenType::LITERAL_STR));
    return new ast\ConstPattern($literal);
  }

  /**
   * @return ast\ConstPattern
   * @throws Error
   */
  private function match_float_const_pattern(): ast\ConstPattern {
    $literal = $this->float_literal($this->next(TokenType::LITERAL_FLOAT));
    return new ast\ConstPattern($literal);
  }

  /**
   * @return ast\ConstPattern
   * @throws Error
   */
  private function match_int_const_pattern(): ast\ConstPattern {
    $literal = $this->int_literal($this->next(TokenType::LITERAL_INT));
    return new ast\ConstPattern($literal);
  }

  /**
   * @return ast\ConstPattern
   * @throws Error
   */
  private function match_bool_const_pattern(): ast\ConstPattern {
    $literal = $this->bool_literal($this->next(TokenType::LITERAL_BOOL));
    return new ast\ConstPattern($literal);
  }

  /**
   * @param Token $if_keyword
   * @return ast\IfExpr
   * @throws Error
   */
  private function if_expr(Token $if_keyword): ast\IfExpr {
    $cond    = $this->expr();
    $if_true = $this->block();

    if ($this->lexer->peek()->type === TokenType::KEYWORD_ELSE) {
      $this->next(TokenType::KEYWORD_ELSE);
      $if_false = $this->block();
    } else {
      $if_false = null;
    }

    $span = $if_keyword->span->extended_to(($if_false ? $if_false : $if_true)->span);
    return new ast\IfExpr($span, $cond, $if_true, $if_false);
  }

  /**
   * @param ast\Expr $callee
   * @return ast\CallExpr
   * @throws Error
   */
  private function call_expr(ast\Expr $callee): ast\CallExpr {
    $args = [];
    while (true) {
      $peek = $this->lexer->peek();
      if ($peek === null || $peek->type === TokenType::PAREN_RIGHT) {
        break;
      }
      $args[] = $this->expr();
      $peek   = $this->lexer->peek();
      if ($peek === null || $peek->type !== TokenType::COMMA) {
        break;
      } else {
        $this->next(TokenType::COMMA);
      }
    }
    $paren_right = $this->next(TokenType::PAREN_RIGHT);
    $span        = $callee->span->extended_to($paren_right->span);
    return new ast\CallExpr($span, $callee, $args);
  }

  /**
   * @param Token $bracket_left
   * @return ast\ListExpr
   * @throws Error
   */
  private function list_expr(Token $bracket_left): ast\ListExpr {
    $elements = [];
    while (true) {
      if ($this->lexer->peek()->type === TokenType::BRACKET_RIGHT) {
        break;
      }

      $elements[] = $this->expr();
      if ($this->lexer->peek()->type !== TokenType::COMMA) {
        break;
      }
      $this->next(TokenType::COMMA);
    }
    $bracket_right = $this->next(TokenType::BRACKET_RIGHT);
    $span          = $bracket_left->span->extended_to($bracket_right->span);
    return new ast\ListExpr($span, $elements);
  }

  /**
   * @return ast\Expr
   * @throws Error
   */
  private function group_expr(): ast\Expr {
    $expr = $this->expr();
    $this->next(TokenType::PAREN_RIGHT);
    return $expr;
  }

  /**
   * @return ast\Expr
   * @throws Error
   */
  private function path_expr(): ast\Expr {
    $path = $this->path_node();
    if ($path->tail instanceof ast\UpperNameNode) {
      return $this->variant_constructor($path);
    } else {
      return new ast\PathExpr($path);
    }
  }

  /**
   * @param ast\PathNode $path
   * @return ast\VariantConstructorExpr
   * @throws Error
   */
  private function variant_constructor(ast\PathNode $path): ast\VariantConstructorExpr {
    switch ($this->lexer->peek()->type) {
      case TokenType::BRACE_LEFT:
        return $this->named_variant_constructor($path);
      case TokenType::PAREN_LEFT:
        return $this->ordered_variant_constructor($path);
      default:
        return $this->unit_variant_constructor($path);
    }
  }

  /**
   * @param ast\PathNode $path
   * @return ast\VariantConstructorExpr
   * @throws Error
   */
  private function named_variant_constructor(ast\PathNode $path): ast\VariantConstructorExpr {
    $brace_left = $this->next(TokenType::BRACE_LEFT);
    $pairs      = [ $this->field_expr() ];
    while ($this->lexer->peek()->type === TokenType::COMMA) {
      $this->next(TokenType::COMMA);
      $pairs[] = $this->field_expr();
    }
    $brace_right = $this->next(TokenType::BRACE_RIGHT);
    $span        = $brace_left->span->extended_to($brace_right->span);
    $fields      = new ast\NamedVariantConstructorFields($span, $pairs);
    $span        = $path->span->extended_to($fields->span);
    return new ast\VariantConstructorExpr($span, $path, $fields);
  }

  /**
   * @param ast\PathNode $path
   * @return ast\VariantConstructorExpr
   * @throws Error
   */
  private function ordered_variant_constructor(ast\PathNode $path): ast\VariantConstructorExpr {
    $paren_left = $this->next(TokenType::PAREN_LEFT);
    $order      = [ $this->expr() ];
    while ($this->lexer->peek()->type === TokenType::COMMA) {
      $this->next(TokenType::COMMA);
      $order[] = $this->expr();
    }
    $paren_right = $this->next(TokenType::PAREN_RIGHT);
    $span        = $paren_left->span->extended_to($paren_right->span);
    $fields      = new ast\OrderedVariantConstructorFields($span, $order);
    $span        = $path->span->extended_to($fields->span);
    return new ast\VariantConstructorExpr($span, $path, $fields);
  }

  /**
   * @param ast\PathNode $path
   * @return ast\VariantConstructorExpr
   */
  private function unit_variant_constructor(ast\PathNode $path): ast\VariantConstructorExpr {
    return new ast\VariantConstructorExpr($path->span, $path, null);
  }

  /**
   * @param Token $str
   * @return ast\StrLiteral
   */
  private function str_literal(Token $str): ast\StrLiteral {
    $value = substr($str->lexeme, 1, -1);
    return new ast\StrLiteral($str->span, $value, $str->lexeme);
  }

  /**
   * @param Token $float
   * @return ast\FloatLiteral
   */
  private function float_literal(Token $float): ast\FloatLiteral {
    $value     = floatval($float->lexeme);
    $precision = strlen(explode('.', $float->lexeme)[1]);
    return new ast\FloatLiteral($float->span, $value, $precision, $float->lexeme);
  }

  /**
   * @param Token $int
   * @return ast\IntLiteral
   */
  private function int_literal(Token $int): ast\IntLiteral {
    $value = intval($int->lexeme, 10);
    return new ast\IntLiteral($int->span, $value, $int->lexeme);
  }

  /**
   * @param Token $bool
   * @return ast\BoolLiteral
   */
  private function bool_literal(Token $bool): ast\BoolLiteral {
    $value = $bool->lexeme === 'true';
    return new ast\BoolLiteral($bool->span, $value, $bool->lexeme);
  }

  /**
   * Other nodes
   */

  /**
   * ( :: )? ( UPPER_NAME :: )* UPPER_NAME ( :: ( LOWER_NAME | STAR ) )?
   *
   * @return ast\CompoundPathNode
   * @throws Error
   */
  private function compound_path_node(): ast\CompoundPathNode {
    $extern = false;
    $span   = null;
    if ($this->lexer->peek()->type === TokenType::DOUBLE_COLON) {
      $extern        = true;
      $extern_colons = $this->next(TokenType::DOUBLE_COLON);
      $span          = $extern_colons->span;
    }

    $body = [];
    while (true) {
      $tail = ast\UpperNameNode::from_token($this->next(TokenType::UPPER_NAME));
      $span = isset($span)
        ? $span->extended_to($tail->span)
        : $tail->span;

      if ($this->lexer->peek()->type === TokenType::DOUBLE_COLON) {
        $this->next(TokenType::DOUBLE_COLON);
        $body[] = $tail;
        if ($this->lexer->peek()->type !== TokenType::UPPER_NAME) {
          break;
        } else {
          continue;
        }
      }

      goto done;
    }

    switch ($this->lexer->peek()->type) {
      case TokenType::STAR:
        $tail = ast\StarSegment::from_token($this->next(TokenType::STAR));
        break;
      default:
        $tail = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));
        break;
    }

    $span = isset($span)
      ? $span->extended_to($tail->span)
      : $tail->span;

    done:
    return new ast\CompoundPathNode($span, $extern, $body, $tail);
  }

  /**
   * ( :: UPPER_NAME :: )? ( UPPER_NAME :: )* ( UPPER_NAME | LOWER_NAME )
   *
   * @return ast\PathNode
   * @throws Error
   */
  private function path_node(): ast\PathNode {
    $extern = false;
    $body   = [];
    $span   = null;

    if ($this->lexer->peek()->type === TokenType::DOUBLE_COLON) {
      $extern = true;
      $colons = $this->next(TokenType::DOUBLE_COLON);
      $span   = $colons->span;
      $body[] = ast\UpperNameNode::from_token($this->next(TokenType::UPPER_NAME));
      $this->next(TokenType::DOUBLE_COLON);
    }

    while ($this->lexer->peek()->type === TokenType::UPPER_NAME) {
      $body[] = ast\UpperNameNode::from_token($this->next(TokenType::UPPER_NAME));
      $span   = isset($span) ? $span : end($body)->span;

      if ($this->lexer->peek()->type === TokenType::DOUBLE_COLON) {
        $this->next(TokenType::DOUBLE_COLON);
      } else {
        break;
      }
    }

    if ($this->lexer->peek()->type === TokenType::LOWER_NAME) {
      $tail = ast\LowerNameNode::from_token($this->next(TokenType::LOWER_NAME));
    } else {
      $tail = array_pop($body);
    }

    $span = isset($span) ? $span->extended_to($tail->span) : $tail->span;
    return new ast\PathNode($span, $extern, $body, $tail);
  }

  /**
   * MISCELLANEOUS
   *
   * Utility methods for digesting tokens or parsing subtrees
   */

  /**
   * @return Token
   * @throws Error
   */
  private function semicolon(): Token {
    $prev = $this->lexer->prev();
    $next = $this->lexer->next();
    if ($next->type === TokenType::SEMICOLON) {
      return $next;
    } else if ($prev !== null) {
      throw Errors::expected_semicolon($prev->span->to->to_span());
    } else {
      throw Errors::expected_token($next, TokenType::SEMICOLON);
    }
  }

  /**
   * @param string $type
   * @return Token
   * @throws Error
   */
  private function next(string $type): Token {
    $next = $this->lexer->next();
    if ($next->type !== $type) {
      throw Errors::expected_token($next, $type);
    } else {
      return $next;
    }
  }

  /**
   * @param Token|null $token
   * @return int
   */
  private function infix_token_precedence(?Token $token): int {
    if ($token === null) {
      return Precedence::LOWEST;
    }

    switch ($token->type) {
      case TokenType::PLUS:
      case TokenType::PLUS_PLUS:
      case TokenType::DASH:
        return Precedence::SUM;
      case TokenType::STAR:
      case TokenType::SLASH:
        return Precedence::PRODUCT;
      case TokenType::CARET:
        return Precedence::EXPONENT;
      case TokenType::PAREN_LEFT:
      case TokenType::BRACKET_LEFT:
        return Precedence::ACCESS;
      case TokenType::DOUBLE_EQUALS:
      case TokenType::LESS_THAN:
      case TokenType::LESS_THAN_EQ:
      case TokenType::GREATER_THAN:
      case TokenType::GREATER_THAN_EQ:
        return Precedence::RELATION;
      default:
        return Precedence::LOWEST;
    }
  }

  /**
   * @return ast\Annotation
   * @throws Error
   */
  private function type_annotation(): ast\Annotation {
    $peek = $this->lexer->peek();
    switch ($peek->type) {
      case TokenType::TYPE_PARAM:
        $prefix = $this->type_param_annotation();
        break;
      case TokenType::UPPER_NAME:
      case TokenType::DOUBLE_COLON:
        $prefix = $this->named_annotation();
        break;
      case TokenType::PAREN_LEFT:
        $prefix = $this->grouped_annotation();
        break;
      case TokenType::BRACKET_LEFT:
        $prefix = $this->list_annotation();
        break;
      default:
        throw Errors::expected_annotation($peek);
    }

    while (true) {
      $peek = $this->lexer->peek();
      switch ($peek->type) {
        case TokenType::THIN_ARROW:
          $prefix = $this->function_annotation($prefix);
          break;
        case TokenType::PAREN_LEFT:
          $prefix = $this->parameterized_annotation($prefix);
          break;
        default:
          return $prefix;
      }
    }
    die('unreachable');
  }

  /**
   * @return ast\TypeParamAnnotation
   * @throws Error
   */
  private function type_param_annotation(): ast\TypeParamAnnotation {
    $token = $this->next(TokenType::TYPE_PARAM);
    $name  = substr($token->lexeme, 1);
    return new ast\TypeParamAnnotation($token->span, $name);
  }

  /**
   * @return ast\NamedAnnotation
   * @throws Error
   */
  private function named_annotation(): ast\NamedAnnotation {
    $path = $this->path_node();
    return new ast\NamedAnnotation($path);
  }

  /**
   * @return ast\Annotation
   * @throws Error
   */
  private function grouped_annotation(): ast\Annotation {
    $paren_left = $this->next(TokenType::PAREN_LEFT);
    $members    = [];
    if ($this->lexer->peek()->type !== TokenType::PAREN_RIGHT) {
      while (true) {
        $members[] = $this->type_annotation();
        if ($this->lexer->peek()->type !== TokenType::COMMA) {
          break;
        }
        $this->next(TokenType::COMMA);
      }
    }
    $paren_right = $this->next(TokenType::PAREN_RIGHT);
    $span        = $paren_left->span->extended_to($paren_right->span);
    switch (count($members)) {
      case 0:
        return new ast\UnitAnnotation($span);
      case 1:
        return new ast\GroupedAnnotation($span, $members[0]);
      default:
        return new ast\TupleAnnotation($span, $members);
    }
  }

  /**
   * @return ast\ListAnnotation
   * @throws Error
   */
  private function list_annotation(): ast\ListAnnotation {
    $bracket_left = $this->next(TokenType::BRACKET_LEFT);
    if ($this->lexer->peek()->type === TokenType::BRACKET_RIGHT) {
      $elements = null;
    } else {
      $elements = $this->type_annotation();
    }
    $bracket_right = $this->next(TokenType::BRACKET_RIGHT);
    $span          = $bracket_left->span->extended_to($bracket_right->span);
    return new ast\ListAnnotation($span, $elements);
  }

  /**
   * @param ast\Annotation $prefix
   * @return ast\FunctionAnnotation
   * @throws Error
   */
  private function function_annotation(ast\Annotation $prefix): ast\FunctionAnnotation {
    if ($prefix instanceof ast\GroupedAnnotation) {
      $inputs = [ $prefix->inner ];
    } else if ($prefix instanceof ast\TupleAnnotation) {
      $inputs = $prefix->members;
    } else {
      $inputs = [ $prefix ];
    }
    $this->next(TokenType::THIN_ARROW);
    $output = $this->type_annotation();
    $span   = $prefix->span->extended_to($output->span);
    return new ast\FunctionAnnotation($span, $inputs, $output);
  }

  /**
   * @param ast\Annotation $prefix
   * @return ast\ParameterizedAnnotation
   * @throws Error
   * @noinspection DuplicatedCode
   */
  private function parameterized_annotation(ast\Annotation $prefix): ast\ParameterizedAnnotation {
    $this->next(TokenType::PAREN_LEFT);
    $params = [ $this->type_annotation() ];
    while ($this->lexer->peek()->type === TokenType::COMMA) {
      $this->next(TokenType::COMMA);
      $params[] = $this->type_annotation();
    }
    $paren_right = $this->next(TokenType::PAREN_RIGHT);
    $span        = $prefix->span->extended_to($paren_right->span);
    return new ast\ParameterizedAnnotation($span, $prefix, $params);
  }
}
