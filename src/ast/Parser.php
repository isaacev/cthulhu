<?php

namespace Cthulhu\ast;

use Cthulhu\err\Error;
use Cthulhu\loc\Point;
use Cthulhu\loc\Span;

const RESERVED_WORDS = [
  'else',
  'false',
  'fn',
  'if',
  'let',
  'match',
  'mod',
  'native',
  'true',
  'type',
  'use',
];

const ESCAPE_KEYS = [
  'n' => "\n",
  't' => "\t",
  '\\' => '\\',
];

/**
 * INFO: if more than one operator begin with the same characters, the operator
 * with more characters in total must appear *BEFORE* the operator with fewer
 * characters.
 */
const INFIX_PRECEDENCE_TABLE = [
  '++' => Precedence::SUM,
  '+' => Precedence::SUM,
  '-' => Precedence::SUM,
  '*' => Precedence::PRODUCT,
  '/' => Precedence::PRODUCT,
  '^' => Precedence::EXPONENT,
  '(' => Precedence::ACCESS,
  '==' => Precedence::RELATION,
  '<=' => Precedence::RELATION,
  '<' => Precedence::RELATION,
  '>=' => Precedence::RELATION,
  '>' => Precedence::RELATION,
  '|>' => Precedence::PIPE,
];

class Parser {
  private Lexer $lexer;

  public function __construct(Lexer $lexer) {
    $this->lexer = $lexer;
  }

  /**
   * @return bool
   * @throws Error
   */
  private function ahead_is_eof(): bool {
    return $this->lexer->peek() instanceof TerminalToken;
  }

  /**
   * @param string $delim
   * @return bool
   * @throws Error
   */
  private function ahead_is_delim(string $delim): bool {
    return (
      $this->lexer->peek() instanceof DelimToken &&
      $this->lexer->peek()->lexeme === $delim
    );
  }

  /**
   * @return bool
   * @throws Error
   */
  private function ahead_is_right_delim(): bool {
    $token = $this->lexer->peek();
    return (
      $token instanceof DelimToken &&
      $token->is_left() === false
    );
  }

  /**
   * @param string $delim
   * @return DelimToken
   * @throws Error
   */
  private function next_delim(string $delim): DelimToken {
    $next = $this->lexer->next();
    if ($next instanceof DelimToken && $next->lexeme === $delim) {
      return $next;
    }
    throw Errors::expected_token($next, $delim);
  }

  /**
   * @param string $punct
   * @return bool
   * @throws Error
   */
  private function ahead_is_punct(string $punct): bool {
    $tokens = $this->lexer->peek_multiple(strlen($punct));
    for ($i = 0, $len = strlen($punct); $i < $len; $i++) {
      $tok  = $tokens[$i];
      $char = $punct[$i];
      if (
        $tok instanceof PunctToken &&
        $tok->lexeme === $char &&
        ($i === $len - 1 || $tok->is_joint)
      ) {
        continue;
      }
      return false;
    }
    return true;
  }

  /**
   * @param string $punct
   * @return PunctToken[]
   * @throws Error
   */
  private function next_punct(string $punct): array {
    $tokens = $this->lexer->next_multiple(strlen($punct));
    for ($i = 0, $len = strlen($punct); $i < $len; $i++) {
      for ($i = 0, $len = strlen($punct); $i < $len; $i++) {
        $tok  = $tokens[$i];
        $char = $punct[$i];
        if (
          $tok instanceof PunctToken &&
          $tok->lexeme === $char &&
          ($i === $len - 1 || $tok->is_joint)
        ) {
          continue;
        }
        throw Errors::expected_token($tok, $char);
      }
      return $tokens;
    }
    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  /**
   * @return PunctToken[]
   * @throws Error
   */
  private function peek_longest_punct(): array {
    $tokens = [];
    do {
      $tok = $this->lexer->peek_ahead_by(count($tokens) + 1);
      if ($tok instanceof PunctToken) {
        $tokens[] = $tok;
      } else {
        break;
      }
    } while ($tok->is_joint);

    return $tokens;
  }

  /**
   * @return PunctToken[]
   * @throws Error
   */
  private function next_longest_punct(): array {
    return $this->lexer->next_multiple(count($this->peek_longest_punct()));
  }

  /**
   * @param string $punct
   * @return Span
   * @throws Error
   */
  private function next_punct_span(string $punct): Span {
    $tokens = $this->next_punct($punct);
    return Span::join(...$tokens);
  }

  /**
   * @return PunctToken
   * @throws Error
   */
  private function next_semicolon(): PunctToken {
    $next = $this->lexer->next();
    if ($next instanceof PunctToken && $next->lexeme === ';') {
      return $next;
    }
    throw Errors::expected_token($next, 'semicolon');
  }

  /**
   * @param string $keyword
   * @return bool
   * @throws Error
   */
  private function ahead_is_keyword(string $keyword): bool {
    assert(in_array($keyword, RESERVED_WORDS));
    return (
      $this->lexer->peek() instanceof IdentToken &&
      $this->lexer->peek()->lexeme === $keyword
    );
  }

  /**
   * @param string $keyword
   * @return IdentToken
   * @throws Error
   */
  private function next_keyword(string $keyword): IdentToken {
    $next = $this->lexer->next();
    if ($next instanceof IdentToken && $next->lexeme === $keyword) {
      return $next;
    }
    throw Errors::expected_token($next, "'$keyword' keyword");
  }

  /**
   * @return bool
   * @throws Error
   */
  private function ahead_is_ident(): bool {
    return $this->lexer->peek() instanceof IdentToken;
  }

  /**
   * @return bool
   * @throws Error
   */
  private function ahead_is_lower_ident(): bool {
    $tok = $this->lexer->peek();
    return $tok instanceof IdentToken && $tok->is_lowercase();
  }

  /**
   * @return bool
   * @throws Error
   */
  private function ahead_is_upper_ident(): bool {
    $tok = $this->lexer->peek();
    return $tok instanceof IdentToken && $tok->is_uppercase();
  }

  /**
   * @return IdentToken
   * @throws Error
   */
  private function next_lower_ident(): IdentToken {
    $next = $this->lexer->next();
    if ($next instanceof IdentToken && $next->is_lowercase()) {
      if (in_array($next->lexeme, RESERVED_WORDS)) {
        throw Errors::used_reserved_ident($next);
      }
      return $next;
    }
    throw Errors::expected_token($next, 'lowercase identifier');
  }

  /**
   * @return nodes\LowerNameNode
   * @throws Error
   */
  private function next_lower_name(): nodes\LowerNameNode {
    $token = $this->next_lower_ident();
    return new nodes\LowerNameNode($token->span, $token->lexeme);
  }

  /**
   * @return IdentToken
   * @throws Error
   */
  private function next_upper_ident(): IdentToken {
    $next = $this->lexer->next();
    if ($next instanceof IdentToken && $next->is_uppercase()) {
      if (in_array($next->lexeme, RESERVED_WORDS)) {
        throw Errors::used_reserved_ident($next);
      }
      return $next;
    }
    throw Errors::expected_token($next, 'uppercase identifier');
  }

  /**
   * @return nodes\UpperNameNode
   * @throws Error
   */
  private function next_upper_name(): nodes\UpperNameNode {
    $token = $this->next_upper_ident();
    return new nodes\UpperNameNode($token->span, $token->lexeme);
  }

  /**
   * @return bool
   * @throws Error
   */
  private function ahead_is_literal(): bool {
    return $this->lexer->peek() instanceof LiteralToken;
  }

  /**
   * @param Token[] $tokens
   * @return string
   */
  private function string_from_tokens(array $tokens): string {
    $out = '';
    foreach ($tokens as $token) {
      $out .= $token->lexeme;
    }
    return $out;
  }

  /**
   *
   * THE BEGINNING OF THE NON-TERMINAL PARSERS
   *
   */

  /**
   * @return nodes\File
   * @throws Error
   */
  public function file(): nodes\File {
    $items = $this->items();
    $span  = empty($items) ? $this->lexer->peek()->span : Span::join(...$items);
    return new nodes\File($span, $items);
  }

  /**
   * @return nodes\Item[]
   * @throws Error
   */
  private function items(): array {
    $items = [];
    while (true) {
      if ($this->ahead_is_eof() || $this->ahead_is_delim('}')) {
        break;
      }
      $items[] = $this->item();
    }
    return $items;
  }

  /**
   * @return nodes\Item
   * @throws Error
   */
  private function item(): nodes\Item {
    $attrs = $this->attributes();
    switch (true) {
      case $this->ahead_is_keyword('use'):
        return $this->use_item($attrs);
      case $this->ahead_is_keyword('mod'):
        return $this->mod_item($attrs);
      case $this->ahead_is_keyword('fn'):
        return $this->fn_item($attrs);
      case $this->ahead_is_keyword('native'):
        return $this->native_item($attrs);
      case $this->ahead_is_keyword('type'):
        return $this->type_item($attrs);
      default:
        throw Errors::expected_item($this->lexer->next());
    }
  }

  /**
   * @return nodes\Attribute[]
   * @throws Error
   */
  private function attributes(): array {
    $attrs = [];
    while ($this->ahead_is_punct('#')) {
      $attrs[] = $this->attribute();
    }
    return $attrs;
  }

  /**
   * @return nodes\Attribute
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function attribute(): nodes\Attribute {
    $pound         = $this->next_punct_span('#');
    $left_bracket  = $this->next_delim('[');
    $name          = $this->next_lower_ident()->lexeme;
    $right_bracket = $this->next_delim(']');
    $span          = Span::join($pound, $right_bracket);
    return new nodes\Attribute($span, $name);
  }

  /**
   * @param array $attrs
   * @return nodes\UseItem
   * @throws Error
   */
  private function use_item(array $attrs): nodes\UseItem {
    $keyword = $this->next_keyword('use');
    $path    = $this->compound_path();
    $semi    = $this->next_semicolon();
    $span    = Span::join($keyword, $semi);
    return new nodes\UseItem($span, $path, $attrs);
  }

  /**
   * @param array $attrs
   * @return nodes\ModItem
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function mod_item(array $attrs): nodes\ModItem {
    $keyword     = $this->next_keyword('mod');
    $name        = $this->next_upper_name();
    $brace_left  = $this->next_delim('{');
    $items       = $this->items();
    $brace_right = $this->next_delim('}');
    $span        = Span::join($keyword, $brace_right);
    return new nodes\ModItem($span, $name, $items, $attrs);
  }

  /**
   * @return nodes\ParamNode
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function fn_param(): nodes\ParamNode {
    $name  = $this->next_lower_name();
    $colon = $this->next_punct(':');
    $note  = $this->note();
    $span  = Span::join($name, $note);
    return new nodes\ParamNode($span, $name, $note);
  }

  /**
   * @param array $attrs
   * @return nodes\FnItem
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function fn_item(array $attrs): nodes\FnItem {
    $keyword    = $this->next_keyword('fn');
    $name       = $this->next_lower_name();
    $paren_left = $this->next_delim('(');

    $params = [];
    if ($this->ahead_is_delim(')') === false) {
      $params[] = $this->fn_param();
      while ($this->ahead_is_punct(',')) {
        $comma    = $this->next_punct(',');
        $params[] = $this->fn_param();
      }
    }

    $paren_right = $this->next_delim(')');
    $arrow       = $this->next_punct('->');
    $returns     = $this->note();
    $body        = $this->block();
    $span        = Span::join($keyword, $body);
    return new nodes\FnItem($span, $name, $params, $returns, $body, $attrs);
  }

  /**
   * @param array $attrs
   * @return nodes\Item
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function native_item(array $attrs): nodes\Item {
    $native_keyword = $this->next_keyword('native');
    if ($this->ahead_is_keyword('fn')) {
      $fn_keyword = $this->next_keyword('fn');
      $name       = $this->next_lower_name();
      $note       = $this->func_note($this->grouped_note()); // FIXME
      $semi       = $this->next_semicolon();
      $span       = Span::join($native_keyword, $semi);
      return new nodes\NativeFuncItem($span, $name, $note, $attrs);
    } else {
      $type_keyword = $this->next_keyword('type');
      $name         = $this->next_upper_name();
      $semi         = $this->next_semicolon();
      $span         = Span::join($native_keyword, $semi);
      return new nodes\NativeTypeItem($span, $name, $attrs);
    }
  }

  /**
   * @return nodes\FieldDeclNode
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function field_decl(): nodes\FieldDeclNode {
    $name  = $this->next_lower_name();
    $colon = $this->next_punct(':');
    $note  = $this->note();
    $span  = Span::join($name, $note);
    return new nodes\FieldDeclNode($span, $name, $note);
  }

  /**
   * @param nodes\UpperNameNode $name
   * @return nodes\NamedVariantDeclNode
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function named_variant_decl(nodes\UpperNameNode $name): nodes\NamedVariantDeclNode {
    $brace_left = $this->next_delim('{');
    $fields     = [ $this->field_decl() ];
    while ($this->ahead_is_punct(',')) {
      $comma    = $this->next_punct(',');
      $fields[] = $this->field_decl();
    }
    $brace_right = $this->next_delim('}');
    $span        = Span::join($name, $brace_right);
    return new nodes\NamedVariantDeclNode($span, $name, $fields);
  }

  /**
   * @param nodes\UpperNameNode $name
   * @return nodes\OrderedVariantDeclNode
   * @throws Error
   */
  private function ordered_variant_decl(nodes\UpperNameNode $name): nodes\OrderedVariantDeclNode {
    $paren_left  = $this->next_delim('(');
    $members     = $this->one_or_more_notes();
    $paren_right = $this->next_delim(')');
    $span        = Span::join($paren_left, $paren_right);
    return new nodes\OrderedVariantDeclNode($span, $name, $members);
  }

  private function unit_variant_decl(nodes\UpperNameNode $name): nodes\UnitVariantDeclNode {
    return new nodes\UnitVariantDeclNode($name);
  }

  /**
   * @return nodes\VariantDeclNode
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function variant_decl(): nodes\VariantDeclNode {
    $pipe = $this->next_punct('|');
    $name = $this->next_upper_name();
    if ($this->ahead_is_delim('{')) {
      return $this->named_variant_decl($name);
    } else if ($this->ahead_is_delim('(')) {
      return $this->ordered_variant_decl($name);
    } else {
      return $this->unit_variant_decl($name);
    }
  }

  /**
   * @param array $attrs
   * @return nodes\UnionItem
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function type_item(array $attrs): nodes\UnionItem {
    $keyword = $this->next_keyword('type');
    $name    = $this->next_upper_name();
    if ($this->ahead_is_delim('(')) {
      $paren_left  = $this->next_delim('(');
      $params      = $this->zero_or_more_notes();
      $paren_right = $this->next_delim(')');
    } else {
      $params = [];
    }
    $equals   = $this->next_punct('=');
    $variants = [ $this->variant_decl() ];
    while ($this->ahead_is_punct('|')) {
      $variants[] = $this->variant_decl();
    }
    $semi = $this->next_semicolon();
    $span = Span::join($keyword, $semi);
    return new nodes\UnionItem($span, $name, $params, $variants, $attrs);
  }

  /**
   * @return nodes\BlockNode
   * @throws Error
   */
  private function block(): nodes\BlockNode {
    $brace_left  = $this->next_delim('{');
    $stmts       = $this->stmts();
    $brace_right = $this->next_delim('}');
    $span        = Span::join($brace_left, $brace_right);
    return new nodes\BlockNode($span, $stmts);
  }

  /**
   * @return nodes\Stmt[]
   * @throws Error
   */
  private function stmts(): array {
    $stmts = [];
    while (true) {
      if (
        $this->ahead_is_delim('}') ||
        ($stmts[] = $this->stmt()) instanceof nodes\ExprStmt
      ) {
        break;
      }
    }
    return $stmts;
  }

  /**
   * @return nodes\Stmt
   * @throws Error
   */
  private function stmt(): nodes\Stmt {
    $attrs = $this->attributes();
    switch (true) {
      case $this->ahead_is_keyword('let'):
        return $this->let_stmt($attrs);
      default:
        return $this->expr_stmt($attrs);
    }
  }

  /**
   * @param array $attrs
   * @return nodes\LetStmt
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function let_stmt(array $attrs): nodes\LetStmt {
    $keyword = $this->next_keyword('let');
    $name    = $this->next_lower_name();
    $note    = null;

    if ($this->ahead_is_punct(':')) {
      $colon = $this->next_punct(':');
      $note  = $this->note();
    }

    $equals = $this->next_punct('=');
    $expr   = $this->expr();
    $semi   = $this->next_semicolon();
    $span   = Span::join($keyword, $semi);
    return new nodes\LetStmt($span, $name, $note, $expr, $attrs);
  }

  /**
   * @param array $attrs
   * @return nodes\Stmt
   * @throws Error
   */
  private function expr_stmt(array $attrs): nodes\Stmt {
    $expr = $this->expr();
    if ($this->ahead_is_punct(';')) {
      $semi = $this->next_semicolon();
      return new nodes\SemiStmt($expr, $semi, $attrs);
    } else {
      return new nodes\ExprStmt($expr, $attrs);
    }
  }

  /**
   * @param int $threshold
   * @return nodes\Expr
   * @throws Error
   */
  private function expr(int $threshold = Precedence::LOWEST): nodes\Expr {
    $prefix = $this->prefix_expr();
    while ($threshold < $this->ahead_infix_precedence()) {
      $prefix = $this->postfix_expr($prefix);
    }
    return $prefix;
  }

  /**
   * @return string|null
   * @throws Error
   */
  private function longest_infix_operator(): ?string {
    // TODO:
    // This seems is inefficient. Is the some way that operators could be
    // represented in some sort of trie?
    foreach (array_keys(INFIX_PRECEDENCE_TABLE) as $operator) {
      if ($this->ahead_is_punct($operator)) {
        return $operator;
      }
    }
    return null;
  }

  /**
   * @return int
   * @throws Error
   */
  private function ahead_infix_precedence(): int {
    if ($this->ahead_is_delim('(')) {
      return INFIX_PRECEDENCE_TABLE['('];
    } else if ($this->ahead_is_delim('[')) {
      return INFIX_PRECEDENCE_TABLE['['];
    }

    if ($infix_operator = $this->longest_infix_operator()) {
      return INFIX_PRECEDENCE_TABLE[$infix_operator];
    }

    return Precedence::LOWEST;
  }

  /**
   * @return nodes\Expr
   * @throws Error
   */
  private function prefix_expr(): nodes\Expr {
    switch (true) {
      case $this->ahead_is_keyword('match'):
        return $this->match_expr();
      case $this->ahead_is_keyword('if'):
        return $this->if_expr();
      case $this->ahead_is_delim('['):
        return $this->list_expr();
      case $this->ahead_is_delim('('):
        return $this->paren_expr();
      case $this->ahead_is_punct('-'):
        return $this->unary_prefix_expr();
      case $this->ahead_is_ident():
      case $this->ahead_is_punct('::'):
        return $this->path_expr();
      case $this->ahead_is_literal():
        return $this->literal_expr();
      default:
        throw Errors::expected_expression($this->lexer->next());
    }
  }

  /**
   * @return nodes\MatchArm
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function match_arm(): nodes\MatchArm {
    $pattern = $this->pattern();
    $arrow   = $this->next_punct('=>');
    $handler = $this->expr();
    $comma   = $this->next_punct(',');
    $span    = Span::join($pattern, ...$comma);
    return new nodes\MatchArm($span, $pattern, $handler);
  }

  /**
   * @return nodes\MatchArm[]
   * @throws Error
   */
  private function match_arms(): array {
    $arms = [ $this->match_arm() ];
    while ($this->ahead_is_delim('}') === false) {
      $arms[] = $this->match_arm();
    }
    return $arms;
  }

  /**
   * @return nodes\MatchExpr
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function match_expr(): nodes\MatchExpr {
    $keyword      = $this->next_keyword('match');
    $discriminant = $this->expr();
    $brace_left   = $this->next_delim('{');
    $arms         = $this->match_arms();
    $brace_right  = $this->next_delim('}');
    $span         = Span::join($keyword, $brace_right);
    return new nodes\MatchExpr($span, $discriminant, $arms);
  }

  /**
   * @return nodes\IfExpr
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function if_expr(): nodes\IfExpr {
    $if_keyword = $this->next_keyword('if');
    $condition  = $this->expr();
    $consequent = $this->block();

    if ($this->ahead_is_keyword('else')) {
      $else_keyword = $this->next_keyword('else');
      $alternate    = $this->block();
    } else {
      $alternate = null;
    }

    $span = Span::join($if_keyword, $alternate ?? $consequent);
    return new nodes\IfExpr($span, $condition, $consequent, $alternate);
  }

  /**
   * @return nodes\ListExpr
   * @throws Error
   */
  private function list_expr(): nodes\ListExpr {
    $bracket_left  = $this->next_delim('[');
    $elements      = $this->zero_or_more_exprs();
    $bracket_right = $this->next_delim(']');
    $span          = Span::join($bracket_left, $bracket_right);
    return new nodes\ListExpr($span, $elements);
  }

  /**
   * @return nodes\Expr
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function paren_expr(): nodes\Expr {
    $paren_left = $this->next_delim('(');
    if ($this->ahead_is_delim(')')) {
      $paren_right = $this->next_delim(')');
      $span        = Span::join($paren_left, $paren_right);
      return new nodes\UnitLiteral($span);
    }

    $expr        = $this->expr();
    $paren_right = $this->next_delim(')');
    return $expr;
  }

  /**
   * @return nodes\UnaryExpr
   * @throws Error
   */
  private function unary_prefix_expr(): nodes\UnaryExpr {
    $operator = $this->next_punct_span('-');
    $operand  = $this->expr(Precedence::UNARY);
    $span     = Span::join($operator, $operand);
    return new nodes\UnaryExpr($span, '-', $operand);
  }

  /**
   * @return nodes\Expr
   * @throws Error
   */
  private function path_expr(): nodes\Expr {
    $path = $this->path();
    if ($path->tail instanceof nodes\UpperNameNode) {
      return $this->constructor_expr($path);
    } else {
      return new nodes\PathExpr($path);
    }
  }

  /**
   * @return nodes\Literal
   * @throws Error
   */
  private function literal_expr(): nodes\Literal {
    $token = $this->lexer->next();
    $span  = $token->span;
    if ($token instanceof StringToken) {
      $value = substr($token->lexeme, 1, -1);
      $raw   = $token->lexeme;
      return new nodes\StrLiteral($span, $value, $raw);
    } else if ($token instanceof FloatToken) {
      $value     = floatval($token->lexeme);
      $precision = $token->precision;
      $raw       = $token->lexeme;
      return new nodes\FloatLiteral($span, $value, $precision, $raw);
    } else if ($token instanceof IntegerToken) {
      $value = intval($token->lexeme, 10);
      $raw   = $token->lexeme;
      return new nodes\IntLiteral($span, $value, $raw);
    } else if ($token instanceof BooleanToken) {
      $value = $token->lexeme === 'true';
      return new nodes\BoolLiteral($span, $value, $token->lexeme);
    }

    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  /**
   * @param Span   $span
   * @param string $raw
   * @return string
   * @throws Error
   */
  private function unescape_raw_string(Span $span, string $raw): string {
    $len = strlen($raw);
    assert($len >= 2 && $raw[0] === '"' && $raw[$len - 1] === '"');

    $value  = '';
    $is_esc = false;
    for ($i = 1; $i < $len - 1; $i++) {
      $char = $raw[$i];
      if ($is_esc) {
        if (array_key_exists($char, ESCAPE_KEYS)) {
          $value  .= ESCAPE_KEYS[$char];
          $is_esc = false;
          continue;
        } else {
          $file = $span->from()->file;
          $line = $span->from()->line;
          $from = new Point($file, $line, $span->from() + $i - 1);
          $to   = new Point($file, $line, $span->from() + $i + 1);
          $span = new Span($from, $to);
          throw Errors::unknown_escape_char($span);
        }
      } else if ($char === '\\') {
        $is_esc = true;
        continue;
      } else {
        $value .= $char;
      }
    }

    return $value;
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\VariantConstructorExpr
   * @throws Error
   */
  private function constructor_expr(nodes\PathNode $path): nodes\VariantConstructorExpr {
    if ($this->ahead_is_delim('{')) {
      return $this->named_constructor_expr($path);
    } else if ($this->ahead_is_delim('(')) {
      return $this->ordered_constructor_expr($path);
    } else {
      return $this->unit_constructor_expr($path);
    }
  }

  /**
   * @return nodes\FieldExprNode
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function field_expr(): nodes\FieldExprNode {
    $name  = $this->next_lower_name();
    $colon = $this->next_punct(':');
    $expr  = $this->expr();
    $span  = Span::join($name, $expr);
    return new nodes\FieldExprNode($span, $name, $expr);
  }

  /**
   * @return array
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function field_exprs(): array {
    $fields = [ $this->field_expr() ];
    while ($this->ahead_is_punct(',')) {
      $comma    = $this->next_punct(',');
      $fields[] = $this->field_expr();
    }
    return $fields;
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\VariantConstructorExpr
   * @throws Error
   */
  private function named_constructor_expr(nodes\PathNode $path): nodes\VariantConstructorExpr {
    $brace_left  = $this->next_delim('{');
    $pairs       = $this->field_exprs();
    $brace_right = $this->next_delim('}');
    $span        = Span::join($brace_left, $brace_right);
    $fields      = new nodes\NamedVariantConstructorFields($span, $pairs);
    $span        = Span::join($path, $fields);
    return new nodes\VariantConstructorExpr($span, $path, $fields);
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\VariantConstructorExpr
   * @throws Error
   */
  private function ordered_constructor_expr(nodes\PathNode $path): nodes\VariantConstructorExpr {
    $paren_left  = $this->next_delim('(');
    $exprs       = $this->one_or_more_exprs();
    $paren_right = $this->next_delim(')');
    $span        = Span::join($paren_left, $paren_right);
    $fields      = new nodes\OrderedVariantConstructorFields($span, $exprs);
    $span        = Span::join($path, $fields);
    return new nodes\VariantConstructorExpr($span, $path, $fields);
  }

  private function unit_constructor_expr(nodes\PathNode $path): nodes\VariantConstructorExpr {
    return new nodes\VariantConstructorExpr($path->span, $path, null);
  }

  /**
   * @param nodes\Expr $prefix
   * @return nodes\Expr
   * @throws Error
   */
  private function postfix_expr(nodes\Expr $prefix): nodes\Expr {
    if ($this->ahead_is_delim('(')) {
      return $this->call_expr($prefix);
    }

    $infix_operator_tokens = $this->next_longest_punct();
    if (empty($infix_operator_tokens)) {
      throw Errors::expected_expression($this->lexer->next());
    }

    $infix_operator = $this->string_from_tokens($infix_operator_tokens);
    return $this->binary_infix_expr($prefix, $infix_operator);
  }

  /**
   * @param nodes\Expr $prefix
   * @return nodes\CallExpr
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function call_expr(nodes\Expr $prefix): nodes\CallExpr {
    $paren_left  = $this->next_delim('(');
    $args        = $this->zero_or_more_exprs();
    $paren_right = $this->next_delim(')');
    $span        = Span::join($prefix, $paren_right);
    return new nodes\CallExpr($span, $prefix, $args);
  }

  /**
   * @param nodes\Expr $prefix
   * @param string     $infix_operator
   * @return nodes\Expr
   * @throws Error
   */
  private function binary_infix_expr(nodes\Expr $prefix, string $infix_operator): nodes\Expr {
    if ($infix_operator === '|>') {
      return $this->pipe_expr($prefix);
    }

    $postfix = $this->expr(INFIX_PRECEDENCE_TABLE[$infix_operator]);
    $span    = Span::join($prefix, $postfix);
    return new nodes\BinaryExpr($span, $infix_operator, $prefix, $postfix);
  }

  /**
   * @param nodes\Expr $prefix
   * @return nodes\PipeExpr
   * @throws Error
   */
  private function pipe_expr(nodes\Expr $prefix): nodes\PipeExpr {
    $postfix = $this->expr(Precedence::PIPE);
    $span    = Span::join($prefix, $postfix);
    return new nodes\PipeExpr($span, $prefix, $postfix);
  }

  /**
   * @return array
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function one_or_more_notes(): array {
    $notes = [ $this->note() ];
    while ($this->ahead_is_punct(',')) {
      $comma   = $this->next_punct(',');
      $notes[] = $this->note();
    }
    return $notes;
  }

  /**
   * @return nodes\Annotation[]
   * @throws Error
   */
  private function zero_or_more_notes(): array {
    if ($this->ahead_is_right_delim()) {
      return [];
    }
    return $this->one_or_more_notes();
  }

  /**
   * @return nodes\Expr[]
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function one_or_more_exprs(): array {
    $exprs = [ $this->expr() ];
    while ($this->ahead_is_punct(',')) {
      $comma   = $this->next_punct(',');
      $exprs[] = $this->expr();
    }
    return $exprs;
  }

  /**
   * @return nodes\Expr[]
   * @throws Error
   */
  private function zero_or_more_exprs(): array {
    if ($this->ahead_is_right_delim()) {
      return [];
    }
    return $this->one_or_more_exprs();
  }

  /**
   * @return nodes\Pattern
   * @throws Error
   */
  private function pattern(): nodes\Pattern {
    switch (true) {
      case $this->ahead_is_punct('::'):
      case $this->ahead_is_upper_ident():
        return $this->variant_pattern();
      case $this->ahead_is_lower_ident():
        return $this->variable_pattern();
      case $this->ahead_is_literal():
        return $this->const_pattern();
      case $this->ahead_is_punct('_'):
        return $this->wildcard_pattern();
      default:
        throw Errors::expected_pattern($this->lexer->next());
    }
  }

  /**
   * @return nodes\VariantPattern
   * @throws Error
   */
  private function variant_pattern(): nodes\VariantPattern {
    $path = $this->upper_path();
    if ($this->ahead_is_delim('{')) {
      return $this->named_variant_pattern($path);
    } else if ($this->ahead_is_delim('(')) {
      return $this->ordered_variant_pattern($path);
    } else {
      return $this->unit_variant_pattern($path);
    }
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\VariantPattern
   * @throws Error
   */
  private function named_variant_pattern(nodes\PathNode $path): nodes\VariantPattern {
    $brace_left  = $this->next_delim('{');
    $mapping     = $this->named_variant_mapping();
    $brace_right = $this->next_delim('}');
    $span        = Span::join($brace_left, $brace_right);
    $fields      = new nodes\NamedVariantPatternFields($span, $mapping);
    $span        = Span::join($path, $fields);
    return new nodes\VariantPattern($span, $path, $fields);
  }

  /**
   * @return array
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function named_variant_mapping(): array {
    $mapping = [ $this->named_variant_field() ];
    while ($this->ahead_is_punct(',')) {
      $comma     = $this->next_punct(',');
      $mapping[] = $this->named_variant_field();
    }
    return $mapping;
  }

  /**
   * @return nodes\NamedPatternField
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function named_variant_field(): nodes\NamedPatternField {
    $name    = $this->next_lower_name();
    $colon   = $this->next_punct(':');
    $pattern = $this->pattern();
    $span    = Span::join($name, $pattern);
    return new nodes\NamedPatternField($span, $name, $pattern);
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\VariantPattern
   * @throws Error
   */
  private function ordered_variant_pattern(nodes\PathNode $path): nodes\VariantPattern {
    $paren_left  = $this->next_delim('(');
    $order       = $this->ordered_variant_order();
    $paren_right = $this->next_delim(')');
    $span        = Span::join($paren_left, $paren_right);
    $fields      = new nodes\OrderedVariantPatternFields($span, $order);
    $span        = Span::join($path, $fields);
    return new nodes\VariantPattern($span, $path, $fields);
  }

  /**
   * @return array
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function ordered_variant_order(): array {
    $order = [ $this->pattern() ];
    while ($this->ahead_is_punct(',')) {
      $comma   = $this->next_punct(',');
      $order[] = $this->pattern();
    }
    return $order;
  }

  private function unit_variant_pattern(nodes\PathNode $path): nodes\VariantPattern {
    return new nodes\VariantPattern($path->span, $path, null);
  }

  /**
   * @return nodes\VariablePattern
   * @throws Error
   */
  private function variable_pattern(): nodes\VariablePattern {
    $name = $this->next_lower_name();
    return new nodes\VariablePattern($name);
  }

  /**
   * @return nodes\ConstPattern
   * @throws Error
   */
  private function const_pattern(): nodes\ConstPattern {
    return new nodes\ConstPattern($this->literal_expr());
  }

  /**
   * @return nodes\WildcardPattern
   * @throws Error
   */
  private function wildcard_pattern(): nodes\WildcardPattern {
    $span = $this->next_punct_span('_');
    return new nodes\WildcardPattern($span);
  }

  /**
   * @return nodes\Annotation
   * @throws Error
   */
  private function note(): nodes\Annotation {
    if ($this->ahead_is_punct("'")) {
      $prefix = $this->param_note();
    } else if ($this->ahead_is_ident()) {
      $prefix = $this->named_note();
    } else if ($this->ahead_is_delim('(')) {
      $prefix = $this->grouped_note();
    } else if ($this->ahead_is_delim('[')) {
      $prefix = $this->list_note();
    } else {
      throw Errors::expected_note($this->lexer->peek());
    }

    while ($this->ahead_is_punct('->')) {
      $prefix = $this->func_note($prefix);
    }

    return $prefix;
  }

  /**
   * @return nodes\TypeParamAnnotation
   * @throws Error
   */
  private function param_note(): nodes\TypeParamAnnotation {
    $quote = $this->next_punct("'")[0];
    if ($quote->is_joint === false || $this->ahead_is_lower_ident() === false) {
      throw Errors::unnamed_type_param($quote->span);
    }
    $name = $this->next_lower_name();
    $span = Span::join($quote, $name);
    return new nodes\TypeParamAnnotation($span, $name->ident);
  }

  /**
   * @return nodes\Annotation
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function named_note(): nodes\Annotation {
    $path = $this->path();
    $note = new nodes\NamedAnnotation($path);

    if ($this->ahead_is_delim('(')) {
      $paren_left  = $this->next_delim('(');
      $params      = $this->one_or_more_notes();
      $paren_right = $this->next_delim(')');
      $span        = Span::join($note, $paren_right);
      return new nodes\ParameterizedAnnotation($span, $note, $params);
    }

    return $note;
  }

  /**
   * @return nodes\Annotation
   * @throws Error
   */
  private function grouped_note(): nodes\Annotation {
    $paren_left  = $this->next_delim('(');
    $members     = $this->zero_or_more_notes();
    $paren_right = $this->next_delim(')');
    $span        = Span::join($paren_left, $paren_right);

    switch (count($members)) {
      case 0:
        return new nodes\UnitAnnotation($span);
      case 1:
        return new nodes\GroupedAnnotation($span, $members[0]);
      default:
        return new nodes\TupleAnnotation($span, $members);
    }
  }

  /**
   * @return nodes\ListAnnotation
   * @throws Error
   */
  private function list_note(): nodes\ListAnnotation {
    $left_bracket  = $this->next_delim('[');
    $elements      = $this->note();
    $right_bracket = $this->next_delim(']');
    $span          = Span::join($left_bracket, $right_bracket);
    return new nodes\ListAnnotation($span, $elements);
  }

  /**
   * @param nodes\Annotation $prefix
   * @return nodes\FunctionAnnotation
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function func_note(nodes\Annotation $prefix): nodes\FunctionAnnotation {
    if ($prefix instanceof nodes\GroupedAnnotation) {
      $inputs = [ $prefix->inner ];
    } else if ($prefix instanceof nodes\TupleAnnotation) {
      $inputs = $prefix->members;
    } else {
      $inputs = [ $prefix ];
    }
    $arrow  = $this->next_punct('->');
    $output = $this->note();
    $span   = Span::join($prefix, $output);
    return new nodes\FunctionAnnotation($span, $inputs, $output);
  }

  /**
   * ( :: UPPER_NAME :: )? ( UPPER_NAME :: )*
   *
   * @return array
   * @throws Error
   * @noinspection PhpUnusedLocalVariableInspection
   */
  private function path_head(): array {
    $is_extern = false;
    $body      = [];
    $span      = null;

    if ($this->ahead_is_punct('::')) {
      $is_extern = true;
      $span      = $this->next_punct_span('::');
      $body[]    = $this->next_upper_name();
      $colons    = $this->next_punct('::');
    }

    while ($this->ahead_is_upper_ident()) {
      $body[] = $this->next_upper_name();
      $span   = isset($span) ? $span : end($body)->span;

      if ($this->ahead_is_punct('::')) {
        $colons = $this->next_punct('::');
      } else {
        break;
      }
    }

    return [ $is_extern, $span, $body ];
  }

  /**
   * ( :: UPPER_NAME :: )? ( UPPER_NAME :: )* UPPER_NAME
   *
   * @return nodes\PathNode
   * @throws Error
   */
  private function upper_path(): nodes\PathNode {
    [ $is_extern, $span, $body ] = $this->path_head();

    if (empty($body)) {
      $tail = $this->next_upper_name();
    } else {
      $tail = array_pop($body);
    }

    $span = Span::join($span ?? $tail, $tail);
    return new nodes\PathNode($span, $is_extern, $body, $tail);
  }

  /**
   * ( :: UPPER_NAME :: )? ( UPPER_NAME :: )* ( UPPER_NAME | LOWER_NAME )
   *
   * @return nodes\PathNode
   * @throws Error
   */
  private function path(): nodes\PathNode {
    [ $is_extern, $span, $body ] = $this->path_head();

    if ($this->ahead_is_lower_ident()) {
      $tail = $this->next_lower_name();
    } else if (empty($body)) {
      $tail = $this->next_upper_name();
    } else {
      $tail = array_pop($body);
    }

    $span = Span::join($span ?? $tail, $tail);
    return new nodes\PathNode($span, $is_extern, $body, $tail);
  }

  /**
   * ( :: )? ( UPPER_NAME :: )* UPPER_NAME ( :: ( LOWER_NAME | STAR ) )?
   *
   * @return nodes\CompoundPathNode
   * @throws Error
   */
  private function compound_path(): nodes\CompoundPathNode {
    $extern = false;
    $span   = null;
    if ($this->ahead_is_punct('::')) {
      $extern = true;
      $span   = $this->next_punct_span('::');
    }

    $body = [];
    while (true) {
      $tail = $this->next_upper_name();
      $span = isset($span)
        ? Span::join($span, $tail)
        : $tail->span;

      if ($this->ahead_is_punct('::')) {
        $this->next_punct('::');
        $body[] = $tail;
        if ($this->ahead_is_punct('::')) {
          continue;
        } else {
          break;
        }
      }

      return new nodes\CompoundPathNode($span, $extern, $body, $tail);
    }

    if ($this->ahead_is_punct('*')) {
      $tail = new nodes\StarSegment($this->next_punct_span('*'));
    } else {
      $tail = $this->next_lower_name();
    }

    $span = isset($span)
      ? Span::join($span, $tail)
      : $tail->span;

    return new nodes\CompoundPathNode($span, $extern, $body, $tail);
  }
}
