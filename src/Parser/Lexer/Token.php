<?php

namespace Cthulhu\Parser\Lexer;

class Token {
  public $type;
  public $span;
  public $lexeme;

  function __construct(string $type, Span $span, ?string $lexeme = '') {
    $this->type = $type;
    $this->span = $span;
    $this->lexeme = $lexeme;
  }
}
