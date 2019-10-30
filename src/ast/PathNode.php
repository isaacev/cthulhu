<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class PathNode extends Node {
  public $extern;
  public $head;
  public $tail;

  /**
   * PathNode constructor.
   * @param Source\Span $span
   * @param bool $extern
   * @param UpperNameNode[] $head
   * @param UpperNameNode|LowerNameNode $tail
   */
  function __construct(Source\Span $span, bool $extern, array $head, $tail) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->head = $head;
    $this->tail = $tail;
  }
}
