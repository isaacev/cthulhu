<?php

namespace Cthulhu\ast;

use Cthulhu\loc\Span;

class TerminalToken extends Token {
  public function __construct(Span $span) {
    parent::__construct($span, '');
  }

  public static function from_char(Char $char): self {
    return new TerminalToken($char->point->to_span());
  }
}
