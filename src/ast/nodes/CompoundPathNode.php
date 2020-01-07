<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class CompoundPathNode extends Node {
  public bool $extern;
  public array $body;
  public $tail;

  /**
   * @param Span                      $span
   * @param bool                      $extern
   * @param UpperNameNode[]           $body
   * @param StarSegment|UpperNameNode $tail
   */
  public function __construct(Span $span, bool $extern, array $body, $tail) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->body   = $body;
    $this->tail   = $tail;
  }
}
