<?php

namespace Cthulhu\Parser\Lexer;

use Cthulhu\Source;

class Lexer {
  public static function from_file(Source\File $file): self {
    return new self($file, Scanner::from_file($file));
  }

  private $file;
  public $scanner;
  private $prev = null;
  private $buffer = null;
  private $mode;

  public const MODE_STRICT  = 1; // Throws error if bad syntax found
  public const MODE_RELAXED = 2; // Marks error, keeps lexing if bad syntax found

  function __construct(Source\File $file, Scanner $scanner, int $mode = self::MODE_STRICT) {
    $this->file = $file;
    $this->scanner = $scanner;
    $this->mode = $mode;
  }

  private function is_strict(): bool {
    return $this->mode === self::MODE_STRICT;
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
      $this->prev = $this->buffer;
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
        return $this->next_num($next);
      case $next->is('"'):
        return $this->next_str($next);
      case $next->is_letter():
        return $this->next_word($next);
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
        return $this->next_single_char(TokenType::PLUS, $next);
      case $next->is('-'):
        return $this->next_single_or_double_char(TokenType::DASH, '>', TokenType::THIN_ARROW, $next);
      case $next->is('*'):
        return $this->next_single_char(TokenType::STAR, $next);
      case $next->is('/'):
        return $this->next_single_char(TokenType::SLASH, $next);
      case $next->is(';'):
        return $this->next_single_char(TokenType::SEMICOLON, $next);
      case $next->is('='):
        return $this->next_single_char(TokenType::EQUALS, $next);
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
        if ($this->is_strict()) {
          throw Errors::unexpected_character($this->file, $next);
        } else {
          return new Token(TokenType::ERROR, $next->point->to_span(), $next->char);
        }
    }
  }

  private function next_num(Character $start): Token {
    $lexeme = $start->char;
    $from = $start->point;
    $to = $start->point;

    while ($peek = $this->scanner->peek()) {
      if ($peek->is_digit() === false) {
        break;
      }

      $next = $this->scanner->next();
      $lexeme .= $next->char;
      $to = $next->point;
    }

    $span = new Span($from, $to->next());
    return new Token(TokenType::LITERAL_NUM, $span, $lexeme);
  }

  private function next_str(Character $start): Token {
    $lexeme = $start->char;
    $from = $start->point;

    while ($peek = $this->scanner->peek()) {
      if ($peek->is('"') || $peek->is(PHP_EOL) || $peek->is_eof()) {
        break;
      }

      $next = $this->scanner->next();
      $lexeme .= $next->char;
    }

    $last = $this->scanner->next();
    if ($last->is_eof() || $last->is(PHP_EOL)) {
      if ($this->is_strict()) {
        $span = $last->point->to_span();
        throw Errors::unclosed_string($this->file, $span);
      } else {
        $span = new Span($from, $last->point);
        return new Token(TokenType::ERROR, $span, $lexeme);
      }
    }

    $lexeme .= $last->char;
    $to = $last->point;
    $span = new Span($from, $to->next());
    return new Token(TokenType::LITERAL_STR, $span, $lexeme);
  }

  private function next_word(Character $start): Token {
    $lexeme = $start->char;
    $from = $start->point;
    $to = $start->point;

    while ($peek = $this->scanner->peek()) {
      if ($peek->is_letter() === false) {
        break;
      }

      $next = $this->scanner->next();
      $lexeme .= $next->char;
      $to = $next->point;
    }

    $span = new Span($from, $to->next());
    switch ($lexeme) {
      case 'let':  return new Token(TokenType::KEYWORD_LET, $span, 'let');
      case 'if':   return new Token(TokenType::KEYWORD_IF, $span, 'if');
      case 'else': return new Token(TokenType::KEYWORD_ELSE, $span, 'else');
      case 'fn':   return new Token(TokenType::KEYWORD_FN, $span, 'fn');
      case 'use':  return new Token(TokenType::KEYWORD_USE, $span, 'use');
      case 'mod':  return new Token(TokenType::KEYWORD_MOD, $span, 'mod');
      default:     return new Token(TokenType::IDENT, $span, $lexeme);
    }
  }

  private function next_single_char(string $type, Character $start): Token {
    $span = new Span($start->point, $start->point->next());
    return new Token($type, $span, $start->char);
  }

  private function next_single_or_double_char(string $single_type, string $second, string $double_type, Character $start): Token {
    $peek = $this->scanner->peek();
    if ($peek->is($second)) {
      $span = new Span($start->point, $this->scanner->next()->point->next());
      return new Token($double_type, $span, $start->char . $peek->char);
    } else {
      $span = new Span($start->point, $start->point->next());
      return new Token($single_type, $span, $start->char);
    }
  }

  public static function to_tokens(Source\File $file, int $mode): array {
    $lexer = new Lexer($file, Scanner::from_file($file), $mode);
    $tokens = [];
    while ($lexer->peek()->type !== TokenType::EOF) {
      $tokens[] = $lexer->next();
    }
    return $tokens;
  }
}
