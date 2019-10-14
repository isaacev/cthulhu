<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

abstract class Node implements \JsonSerializable {
  public $span;

  function __construct(Source\Span $span) {
    $this->span = $span;
  }

  abstract public function visit(array $visitor_table): void;
}
