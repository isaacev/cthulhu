<?php

namespace Cthulhu\ast\tokens;

use Cthulhu\loc\Point;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

abstract class Token implements Spanlike {
  public Span $span;
  public string $lexeme;

  public function __construct(Span $span, string $lexeme) {
    $this->span   = $span;
    $this->lexeme = $lexeme;
  }

  public function span(): Span {
    return $this->span;
  }

  public function from(): Point {
    return $this->span->from;
  }

  public function to(): Point {
    return $this->span->to;
  }
}
