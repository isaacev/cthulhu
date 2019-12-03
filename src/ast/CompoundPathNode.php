<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class CompoundPathNode extends Node {
  public bool $extern;
  public array $body;
  public $tail;

  /**
   * @param Source\Span               $span
   * @param bool                      $extern
   * @param UpperNameNode[]           $body
   * @param StarSegment|UpperNameNode $tail
   */
  function __construct(Source\Span $span, bool $extern, array $body, $tail) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->body   = $body;
    $this->tail   = $tail;
  }
}
