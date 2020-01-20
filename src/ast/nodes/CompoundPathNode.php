<?php

namespace Cthulhu\ast\nodes;

class CompoundPathNode extends Node {
  public bool $is_extern;
  public array $body;
  public $tail;

  /**
   * @param bool                            $extern
   * @param UpperName[]                     $body
   * @param StarSegment|LowerName|UpperName $tail
   */
  public function __construct(bool $extern, array $body, $tail) {
    parent::__construct();
    $this->is_extern = $extern;
    $this->body      = $body;
    $this->tail      = $tail;
  }

  public function children(): array {
    return array_merge($this->body, [ $this->tail ]);
  }
}
