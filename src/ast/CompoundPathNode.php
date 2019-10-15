<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class CompoundPathNode extends Node {
  public $extern;
  public $body;
  public $tail;

  function __construct(Source\Span $span, bool $extern, array $body, $tail) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->body = $body;
    $this->tail = $tail;
  }
}
