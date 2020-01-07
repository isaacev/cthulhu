<?php

namespace Cthulhu\ast;

use Cthulhu\loc\Span;

class PunctToken extends Token {
  public bool $is_joint;

  public function __construct(Span $span, string $lexeme, bool $is_joint) {
    parent::__construct($span, $lexeme);
    $this->is_joint = $is_joint;
  }

  public static function from_char(Char $char, bool $is_joint): self {
    return new self($char->point->to_span(), $char->raw_char, $is_joint);
  }
}
