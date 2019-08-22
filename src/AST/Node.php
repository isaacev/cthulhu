<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Point;
use Cthulhu\Parser\Lexer\Span;

abstract class Node implements \JsonSerializable {
  public $span;

  function __construct(Span $span) {
    $this->span = $span;
  }

  abstract public function visit(array $visitor_table): void;

  // @codeCoverageIgnoreStart
  public function from(): Point {
    return $this->span->from;
  }

  public function to(): Point {
    return $this->span->to;
  }
  // @codeCoverageIgnoreEnd
}
