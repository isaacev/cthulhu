<?php

namespace Cthulhu\ast;

use Cthulhu\err\Error;
use Cthulhu\lib\panic\Panic;
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
   * @return tokens\Token
   * @throws Error
   */
  public function next(): tokens\Token {
    if (empty($this->buffer)) {
      return $this->read();
    } else {
      return array_shift($this->buffer);
    }
  }

  /**
   * @return tokens\Token
   * @throws Error
   */
  private function read(): tokens\Token {
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

  private function next_terminal(Char $next): tokens\TerminalToken {
    return tokens\TerminalToken::from_char($next);
  }

  private function next_num_literal(Char $next): tokens\LiteralToken {
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
      return new tokens\FloatToken($span, $lexeme, $precision);
    }

    $to   = $next->point->next_column();
    $span = new Span($from, $to);
    return new tokens\IntegerToken($span, $lexeme);
  }

  /**
   * @param Char $next
   * @return tokens\StringToken
   * @throws Error
   * @noinspection PhpInconsistentReturnPointsInspection
   */
  private function next_str_literal(Char $next): tokens\Token {
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
          return new tokens\ErrorToken($span, $lexeme, 'unclosed string');
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
        return new tokens\StringToken($span, $lexeme);
      } else {
        $lexeme .= $next->raw_char;
        continue;
      }
    }

    Panic::if_reached(__LINE__, __FILE__);
  }

  private function next_ident(Char $next): tokens\Token {
    $lexeme = $next->raw_char;
    $from   = $next->point;

    while (Char::is_alphanumeric($this->scanner->peek())) {
      $next   = $this->scanner->next();
      $lexeme .= $next->raw_char;
    }

    $to   = $next->point->next_column();
    $span = new Span($from, $to);

    if ($lexeme === 'true' || $lexeme === 'false') {
      return new tokens\BooleanToken($span, $lexeme);
    }
    return new tokens\IdentToken($span, $lexeme);
  }

  private function next_delim(Char $next): tokens\DelimToken {
    return tokens\DelimToken::from_char($next);
  }

  /**
   * @param Char $next
   * @return tokens\Token
   * @throws Error
   */
  private function next_punct_or_comment(Char $next): tokens\Token {
    if ($next->raw_char === '-' && $this->scanner->peek()->raw_char === '-') {
      return $this->next_comment($next);
    }

    $is_joint = Char::is_whitespace($this->scanner->peek()) === false;
    return tokens\PunctToken::from_char($next, $is_joint);
  }

  /**
   * @param Char $next
   * @return tokens\Token
   * @throws Error
   */
  private function next_comment(Char $next): tokens\Token {
    $lexeme = $next->raw_char;
    $from   = $next->point;

    while (Char::is_not_one_of([ "\n" ], $this->scanner->peek())) {
      $next   = $this->scanner->next();
      $lexeme .= $next->raw_char;
    }

    if ($this->hide_comments) {
      return $this->read();
    }

    $to   = $next->point->next_column();
    $span = new Span($from, $to);
    return new tokens\CommentToken($span, $lexeme);
  }
}
