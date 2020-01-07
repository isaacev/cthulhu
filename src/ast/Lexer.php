<?php

namespace Cthulhu\ast;

use Cthulhu\err\Error;
use Cthulhu\loc\Span;

class Lexer {
  private Scanner $scanner;
  private array $buffer = [];
  private bool $should_throw;
  private bool $hide_comments;

  public function __construct(Scanner $scanner, bool $should_throw = true, bool $hide_comments = true) {
    $this->scanner       = $scanner;
    $this->should_throw  = $should_throw;
    $this->hide_comments = $hide_comments;
  }

  /**
   * @return Token
   * @throws Error
   */
  public function peek(): Token {
    if (empty($this->buffer)) {
      $this->buffer = [ $this->read() ];
    }

    return $this->buffer[0];
  }

  /**
   * @param int $n
   * @return Token[]
   * @throws Error
   */
  public function peek_multiple(int $n): array {
    for ($i = count($this->buffer); $i < $n; $i++) {
      $this->buffer[] = $this->read();
    }

    return array_slice($this->buffer, 0, $n);
  }

  /**
   * @param int $n
   * @return Token
   * @throws Error
   */
  public function peek_ahead_by(int $n): Token {
    $this->peek_multiple($n);
    return $this->buffer[$n - 1];
  }

  /**
   * @return Token
   * @throws Error
   */
  public function next(): Token {
    if (empty($this->buffer)) {
      return $this->read();
    } else {
      return array_shift($this->buffer);
    }
  }

  /**
   * @param int $n
   * @return Token[]
   * @throws Error
   */
  public function next_multiple(int $n): array {
    for ($i = count($this->buffer); $i < $n; $i++) {
      $this->buffer[] = $this->read();
    }

    return array_splice($this->buffer, 0, $n);
  }

  /**
   * @return Token
   * @throws Error
   */
  private function read(): Token {
    $this->scanner->skip_while([ '\Cthulhu\ast\Char', 'is_whitespace' ]);

    $next = $this->scanner->next();
    switch (true) {
      case Char::is_eof($next):
        return $this->next_terminal($next);
      case Char::is_digit($next):
        return $this->next_num_literal($next);
      case Char::is_double_quote($next):
        return $this->next_str_literal($next);
      case Char::is_letter($next):
        return $this->next_ident($next);
      case Char::is_delim($next);
        return $this->next_delim($next);
      default:
        return $this->next_punct_or_comment($next);
    }
  }

  private function next_terminal(Char $next): TerminalToken {
    return TerminalToken::from_char($next);
  }

  private function next_num_literal(Char $next): LiteralToken {
    $lexeme = $next->raw_char;
    $from   = $next->point;

    while (Char::is_digit($this->scanner->peek())) {
      $next   = $this->scanner->next();
      $lexeme .= $next->raw_char;
    }

    if (Char::is_dot($this->scanner->peek())) {
      $next   = $this->scanner->next();
      $lexeme .= $next->raw_char;

      $precision = 0;
      while (Char::is_digit($this->scanner->peek())) {
        $next   = $this->scanner->next();
        $lexeme .= $next->raw_char;
        $precision++;
      }

      $to   = $next->point->next_column();
      $span = new Span($from, $to);
      return new FloatToken($span, $lexeme, $precision);
    }

    $to   = $next->point->next_column();
    $span = new Span($from, $to);
    return new IntegerToken($span, $lexeme);
  }

  /**
   * @param Char $next
   * @return StringToken
   * @throws Error
   */
  private function next_str_literal(Char $next): Token {
    $lexeme = $next->raw_char;
    $from   = $next->point;
    $is_esc = false;

    while (true) {
      $next = $this->scanner->next();
      if (Char::is_eof($next) || Char::is_newline($next)) {
        if ($this->should_throw) {
          throw Errors::unclosed_string($next->point);
        } else {
          $span = new Span($from, $next->point);
          return new ErrorToken($span, $lexeme, 'unclosed string');
        }
      } else if ($is_esc) {
        $lexeme .= $next->raw_char;
        $is_esc = false;
        continue;
      } else if (Char::is('\\', $next)) {
        $lexeme .= '\\';
        $is_esc = true;
        continue;
      } else if (Char::is_double_quote($next)) {
        $lexeme .= '"';
        $span   = new Span($from, $next->point->next_column());
        return new StringToken($span, $lexeme);
      } else {
        $lexeme .= $next->raw_char;
        continue;
      }
    }

    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  private function next_ident(Char $next): Token {
    $lexeme = $next->raw_char;
    $from   = $next->point;

    while (Char::is_alphanumeric($this->scanner->peek())) {
      $next   = $this->scanner->next();
      $lexeme .= $next->raw_char;
    }

    $to   = $next->point->next_column();
    $span = new Span($from, $to);

    if ($lexeme === 'true' || $lexeme === 'false') {
      return new BooleanToken($span, $lexeme);
    }
    return new IdentToken($span, $lexeme);
  }

  private function next_delim(Char $next): DelimToken {
    return DelimToken::from_char($next);
  }

  /**
   * @param Char $next
   * @return Token
   * @throws Error
   */
  private function next_punct_or_comment(Char $next): Token {
    if ($next->raw_char === '-' && $this->scanner->peek()->raw_char === '-') {
      return $this->next_comment($next);
    }

    $is_joint = Char::is_whitespace($this->scanner->peek()) === false;
    return PunctToken::from_char($next, $is_joint);
  }

  /**
   * @param Char $next
   * @return Token
   * @throws Error
   */
  private function next_comment(Char $next): Token {
    $lexeme = $next->raw_char;
    $from   = $next->point;

    while (Char::is_not_one_of([ "\n" ], $this->scanner->peek())) {
      $next   = $this->scanner->next();
      $lexeme .= $next->raw_char;
    }

    if ($this->hide_comments) {
      return $this->read();
    }

    $to   = $next->point;
    $span = new Span($from, $to);
    return new CommentToken($span, $lexeme);
  }
}
