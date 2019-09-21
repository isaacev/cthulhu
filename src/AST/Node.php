<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

abstract class Node implements \JsonSerializable {
  public $span;

  function __construct(Source\Span $span) {
    $this->span = $span;
  }

  abstract public function visit(array $visitor_table): void;

  // @codeCoverageIgnoreStart
  public function from(): Source\Point {
    return $this->span->from;
  }

  public function to(): Source\Point {
    return $this->span->to;
  }
  // @codeCoverageIgnoreEnd
}
