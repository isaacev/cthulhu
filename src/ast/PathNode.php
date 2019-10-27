<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class PathNode extends Node {
  public $extern;
  public $head;
  public $tail;

  function __construct(Source\Span $span, bool $extern, array $head, IdentNode $tail) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->head = $head;
    $this->tail = $tail;
  }
}
