<?php

namespace Cthulhu\ast\tokens;

use Cthulhu\ast\Char;
use Cthulhu\loc\Span;

class TerminalToken extends Token {
  public function __construct(Span $span) {
    parent::__construct($span, '');
  }

  public static function from_char(Char $char): self {
    return new TerminalToken($char->point->to_span());
  }

  public function __debugInfo() {
    return [
      'type' => 'Terminal',
      'lexeme' => $this->lexeme,
    ];
  }
}
