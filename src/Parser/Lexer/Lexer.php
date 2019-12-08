<?php

namespace Cthulhu\Parser\Lexer;

use Cthulhu\Source;

class Lexer {
  public static function from_file(Source\File $file): self {
    return new self(Scanner::from_file($file));
  }

  public Scanner $scanner;
  private $prev = null;
  private $buffer = null;
  private int $settings;

  public const RELAXED_ERRORS = 0x0001; // Return errors as tokens instead of throwing an error
  public const KEEP_COMMENTS  = 0x0010; // Return comments as tokens instead of ignoring

  function __construct(Scanner $scanner, int $settings = 0) {
    $this->scanner  = $scanner;
    $this->settings = $settings;
  }

  private function is_relaxed(): bool {
    return ($this->settings & self::RELAXED_ERRORS) > 0;
  }

  private function keep_comments(): bool {
    return ($this->settings & self::KEEP_COMMENTS) > 0;
  }

  /**
   * Return the next token but do not advance the lexer.
   */
  public function peek(): Token {
    if ($this->buffer === null) {
      $this->buffer = $this->read();
    }

    return $this->buffer;
  }

  /**
   * Return the previous token but do not advance the lexer.
   */
  public function prev(): ?Token {
    return $this->prev;
  }

  /**
   * Return the next token and advance the lexer.
   */
  public function next(): Token {
    if ($this->buffer === null) {
      $this->prev = $this->read();
    } else {
      $this->prev   = $this->buffer;
      $this->buffer = null;
    }

    return $this->prev;
  }

  protected function read(): Token {
    while ($this->scanner->peek()->is_whitespace()) {
      $this->scanner->next();
    }

    $next = $this->scanner->next();
    if ($next->is_eof()) {
      return new Token(TokenType::EOF, $next->point->to_span(), '');
    }

    switch (true) {
      case $next->is_digit():
        return $this->next_int_or_float($next);
      case $next->is('"'):
        return $this->next_str($next);
      case $next->is("'"):
        return $this->next_type_param($next);
      case $next->is_letter():
        return $this->next_word($next);
      case $next->is('_'):
        return $this->next_single_char(TokenType::UNDERSCORE, $next);
      case $next->is('^'):
        return $this->next_single_char(TokenType::CARET, $next);
      case $next->is('|'):
        return $this->next_single_char(TokenType::PIPE, $next);
      case $next->is('#'):
        return $this->next_single_char(TokenType::POUND, $next);
      case $next->is('{'):
        return $this->next_single_char(TokenType::BRACE_LEFT, $next);
      case $next->is('}'):
        return $this->next_single_char(TokenType::BRACE_RIGHT, $next);
      case $next->is('['):
        return $this->next_single_char(TokenType::BRACKET_LEFT, $next);
      case $next->is(']'):
        return $this->next_single_char(TokenType::BRACKET_RIGHT, $next);
      case $next->is('('):
        return $this->next_single_char(TokenType::PAREN_LEFT, $next);
      case $next->is(')'):
        return $this->next_single_char(TokenType::PAREN_RIGHT, $next);
      case $next->is('+'):
        return $this->next_single_or_double_char(TokenType::PLUS, '+', TokenType::PLUS_PLUS, $next);
      case $next->is('-'):
        return $this->starts_with_dash($next);
      case $next->is('*'):
        return $this->next_single_char(TokenType::STAR, $next);
      case $next->is('/'):
        return $this->next_single_char(TokenType::SLASH, $next);
      case $next->is(';'):
        return $this->next_single_char(TokenType::SEMICOLON, $next);
      case $next->is('='):
        return $this->next_chars($next, TokenType::EQUALS, [
          '=' => TokenType::DOUBLE_EQUALS,
          '>' => TokenType::FAT_ARROW,
        ]);
      case $next->is(':'):
        return $this->next_single_or_double_char(TokenType::COLON, ':', TokenType::DOUBLE_COLON, $next);
      case $next->is(','):
        return $this->next_single_char(TokenType::COMMA, $next);
      case $next->is('.'):
        return $this->next_single_char(TokenType::DOT, $next);
      case $next->is('<'):
        return $this->next_single_or_double_char(TokenType::LESS_THAN, '=', TokenType::LESS_THAN_EQ, $next);
      case $next->is('>'):
        return $this->next_single_or_double_char(TokenType::GREATER_THAN, '=', TokenType::GREATER_THAN_EQ, $next);
      default:
        if ($this->is_relaxed()) {
          return new Token(TokenType::ERROR, $next->point->to_span(), $next->char);
        } else {
          throw Errors::unexpected_character($next);
        }
    }
  }

  private function next_int_or_float(Character $start): Token {
    $lexeme = $start->char;
    $from   = $start->point;
    $to     = $start->point;

    while ($peek = $this->scanner->peek()) {
      if ($peek->is_digit() === false) {
        break;
      }

      $next   = $this->scanner->next();
      $lexeme .= $next->char;
      $to     = $next->point;
    }

    if ($peek && $peek->is('.')) {
      $dot    = $this->scanner->next();
      $lexeme .= $dot->char;
      $to     = $dot->point;

      $next = $this->scanner->next();
      if ($next === null || $next->is_digit() === false) {
        $span = new Source\Span($from, ($next ? $next : $dot)->point);
        if ($this->is_relaxed()) {
          return new Token(TokenType::ERROR, $span, $lexeme);
        } else {
          throw Errors::invalid_float($span);
        }
      } else {
        $lexeme .= $next->char;
        $to     = $next->point;
      }

      while ($peek = $this->scanner->peek()) {
        if ($peek->is_digit() === false) {
          break;
        }

        $next   = $this->scanner->next();
        $lexeme .= $next->char;
        $to     = $next->point;
      }

      $span = new Source\Span($from, $to->next());
      return new Token(TokenType::LITERAL_FLOAT, $span, $lexeme);
    } else {
      $span = new Source\Span($from, $to->next());
      return new Token(TokenType::LITERAL_INT, $span, $lexeme);
    }
  }

  private function next_str(Character $start): Token {
    $lexeme = $start->char;
    $from   = $start->point;

    while ($peek = $this->scanner->peek()) {
      if ($peek->is('"') || $peek->is(PHP_EOL) || $peek->is_eof()) {
        break;
      }

      $next   = $this->scanner->next();
      $lexeme .= $next->char;
    }

    $last = $this->scanner->next();
    if ($last->is_eof() || $last->is(PHP_EOL)) {
      if ($this->is_relaxed()) {
        $span = new Source\Span($from, $last->point);
        return new Token(TokenType::ERROR, $span, $lexeme);
      } else {
        $span = $last->point->to_span();
        throw Errors::unclosed_string($span);
      }
    }

    $lexeme .= $last->char;
    $to     = $last->point;
    $span   = new Source\Span($from, $to->next());
    return new Token(TokenType::LITERAL_STR, $span, $lexeme);
  }

  private function next_type_param(Character $single_quote): Token {
    $lexeme = $single_quote->char;
    $from   = $single_quote->point;
    $to     = $single_quote->point->next();

    if ($this->scanner->peek()->is_letter() === false) {
      $span = new Source\Span($from, $to);
      if ($this->is_relaxed()) {
        return new Token(TokenType::ERROR, $span, $lexeme);
      } else {
        throw Errors::unnamed_type_param($span);
      }
    }

    while ($this->scanner->peek()->is_letter()) {
      $next   = $this->scanner->next();
      $lexeme .= $next->char;
      $to     = $next->point->next();
    }

    $span = new Source\Span($from, $to);
    return new Token(TokenType::TYPE_PARAM, $span, $lexeme);
  }

  private function next_word(Character $start): Token {
    $lexeme = $start->char;
    $from   = $start->point;
    $to     = $start->point;

    while ($peek = $this->scanner->peek()) {
      $peek_char_is_allowed = (
        $peek->is_letter() ||
        $peek->is_digit() ||
        $peek->is('_')
      );

      if ($peek_char_is_allowed === false) {
        break;
      }

      $next   = $this->scanner->next();
      $lexeme .= $next->char;
      $to     = $next->point;
    }

    $span = new Source\Span($from, $to->next());
    switch ($lexeme) {
      case 'let':
        return new Token(TokenType::KEYWORD_LET, $span, 'let');
      case 'if':
        return new Token(TokenType::KEYWORD_IF, $span, 'if');
      case 'else':
        return new Token(TokenType::KEYWORD_ELSE, $span, 'else');
      case 'fn':
        return new Token(TokenType::KEYWORD_FN, $span, 'fn');
      case 'use':
        return new Token(TokenType::KEYWORD_USE, $span, 'use');
      case 'mod':
        return new Token(TokenType::KEYWORD_MOD, $span, 'mod');
      case 'native':
        return new Token(TokenType::KEYWORD_NATIVE, $span, 'native');
      case 'type':
        return new Token(TokenType::KEYWORD_TYPE, $span, 'type');
      case 'match':
        return new Token(TokenType::KEYWORD_MATCH, $span, 'match');
      case 'true':
        return new Token(TokenType::LITERAL_BOOL, $span, 'true');
      case 'false':
        return new Token(TokenType::LITERAL_BOOL, $span, 'false');
    }

    // For non-keyword names, determine the name's capitalization rules
    $type = $start->is_between('A', 'Z')
      ? TokenType::UPPER_NAME
      : TokenType::LOWER_NAME;
    return new Token($type, $span, $lexeme);
  }

  private function next_single_char(string $type, Character $start): Token {
    $span = new Source\Span($start->point, $start->point->next());
    return new Token($type, $span, $start->char);
  }

  private function starts_with_dash(Character $start): Token {
    $peek = $this->scanner->peek();
    if ($peek->is('>')) {
      $span = new Source\Span($start->point, $this->scanner->next()->point->next());
      return new Token(TokenType::THIN_ARROW, $span, '->');
    } else if ($peek->is('-')) {
      $lexeme = '-';
      while ($peek = $this->scanner->peek()) {
        if ($peek->is_eof() || $peek->is(PHP_EOL)) {
          break;
        }

        $next   = $this->scanner->next();
        $lexeme .= $next->char;
        $to     = $next->point;
      }

      $span = new Source\Span($start->point, $to->next());

      if ($this->keep_comments()) {
        return new Token(TokenType::COMMENT, $span, $lexeme);
      } else {
        return $this->read();
      }
    } else {
      $span = new Source\Span($start->point, $start->point->next());
      return new Token(TokenType::DASH, $span, '-');
    }
  }

  private function next_chars(Character $start, string $single, array $seconds): Token {
    $peek = $this->scanner->peek();
    if (array_key_exists($peek->char, $seconds)) {
      $span = new Source\Span($start->point, $this->scanner->next()->point->next());
      return new Token($seconds[$peek->char], $span, $start->char . $peek->char);
    } else {
      $span = new Source\Span($start->point, $start->point->next());
      return new Token($single, $span, $start->char);
    }
  }

  private function next_single_or_double_char(string $single_type, string $second, string $double_type, Character $start): Token {
    $peek = $this->scanner->peek();
    if ($peek->is($second)) {
      $span = new Source\Span($start->point, $this->scanner->next()->point->next());
      return new Token($double_type, $span, $start->char . $peek->char);
    } else {
      $span = new Source\Span($start->point, $start->point->next());
      return new Token($single_type, $span, $start->char);
    }
  }

  public static function to_tokens(Source\File $file, int $settings): array {
    $lexer  = new Lexer(Scanner::from_file($file), $settings);
    $tokens = [];
    while ($lexer->peek()->type !== TokenType::EOF) {
      $tokens[] = $lexer->next();
    }
    return $tokens;
  }
}
