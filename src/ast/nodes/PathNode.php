<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class PathNode extends Node {
  public bool $extern;
  public array $head;
  public $tail;

  /**
   * @param Span                        $span
   * @param bool                        $extern
   * @param UpperNameNode[]             $head
   * @param UpperNameNode|LowerNameNode $tail
   */
  public function __construct(Span $span, bool $extern, array $head, $tail) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->head   = $head;
    $this->tail   = $tail;
  }
}
