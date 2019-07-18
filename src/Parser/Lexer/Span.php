<?php

namespace Cthulhu\Parser\Lexer;

class Span {
  public $from;
  public $to;

  function __construct(Point $from, Point $to) {
    $this->from = $from;
    $this->to = $to;
  }

  public function __toString() {
    return "$this->from";
  }
}
