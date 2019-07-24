<?php

namespace Cthulhu\Parser\Lexer;

class Lexer {
  private $scanner;
  private $buffer = null;

  function __construct($scanner) {
    $this->scanner = $scanner;
  }

  public function peek(): ?Token {
    if ($this->buffer === null) {
      $this->buffer = $this->next();
    }

    return $this->buffer;
  }

  public function next(): ?Token {
    if ($this->buffer !== null) {
      $buf = $this->buffer;
      $this->buffer = null;
      return $buf;
    }

    while ($this->scanner->peek() && $this->scanner->peek()->is_whitespace()) {
      $this->scanner->next();
    }

    $next = $this->scanner->next();
    if ($next === null) {
      return null;
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
        return $this->next_single_char(TokenType::DASH, $next);
      case $next->is('*'):
        return $this->next_single_char(TokenType::STAR, $next);
      case $next->is('/'):
        return $this->next_single_char(TokenType::SLASH, $next);
      case $next->is(';'):
        return $this->next_single_char(TokenType::SEMICOLON, $next);
      case $next->is('='):
        return $this->next_single_char(TokenType::EQUALS, $next);
      case $next->is(':'):
        return $this->next_single_char(TokenType::COLON, $next);
      case $next->is(','):
        return $this->next_single_char(TokenType::COMMA, $next);
      case $next->is('<'):
        return $this->next_starts_with_less_than($next);
      case $next->is('>'):
        return $this->next_starts_with_greater_than($next);
      default:
        throw new \Exception("unknown character '$next->char' at $next->point");
    }
  }

  private function next_num(Character $start): Token {
    $lexeme = $start->char;
    $from = $start->point;
    $to = $start->point;

    while (true) {
      $peek = $this->scanner->peek();
      if ($peek === null || $peek->is_digit() === false) {
        break;
      }

      $next = $this->scanner->next();
      $lexeme .= $next->char;
      $to = $next->point;
    }

    $span = new Span($from, $to);
    return new Token(TokenType::LITERAL_NUM, $span, $lexeme);
  }

  private function next_str(Character $start): Token {
    $lexeme = $start->char;
    $from = $start->point;

    while ($peek = $this->scanner->peek()) {
      if ($peek->is('"') || $peek->is(PHP_EOL)) {
        break;
      }

      $next = $this->scanner->next();
      $lexeme .= $next->char;
    }

    $last = $this->scanner->next();
    if ($last === null) {
      throw new \Exception("unexpected end of file, unclosed string");
    } else if ($last->is('"') === false) {
      throw new \Exception("unclosed string at offset $from");
    }

    $lexeme .= $last->char;
    $to = $last->point;
    $span = new Span($from, $to);
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

    $span = new Span($from, $to);
    switch ($lexeme) {
      case 'let':  return new Token(TokenType::KEYWORD_LET, $span);
      case 'if':   return new Token(TokenType::KEYWORD_IF, $span);
      case 'else': return new Token(TokenType::KEYWORD_ELSE, $span);
      case 'fn':   return new Token(TokenType::KEYWORD_FN, $span);
      default:     return new Token(TokenType::IDENT, $span, $lexeme);
    }
  }

  private function next_single_char(string $type, Character $start): Token {
    $span = new Span($start->point, $start->point);
    return new Token($type, $span);
  }

  private function next_starts_with_less_than(Character $start): Token {
    $peek = $this->scanner->peek();
    if ($peek && $peek->is('=')) {
      $span = new Span($start->point, $this->scanner->next()->point);
      return new Token(TokenType::LESS_THAN_EQ, $span);
    } else {
      $span = new Span($start->point, $start->point);
      return new Token(TokenType::LESS_THAN, $span);
    }
  }

  private function next_starts_with_greater_than(Character $start): Token {
    $peek = $this->scanner->peek();
    if ($peek && $peek->is('=')) {
      $span = new Span($start->point, $this->scanner->next()->point);
      return new Token(TokenType::GREATER_THAN_EQ, $span);
    } else {
      $span = new Span($start->point, $start->point);
      return new Token(TokenType::GREATER_THAN, $span);
    }
  }

  public static function from_string(string $text): Lexer {
    return new Lexer(new Scanner($text));
  }
}
