<?php

namespace Cthulhu\ast\nodes;

class CompoundPathNode extends Node {
  public bool $is_extern;
  public array $super;
  public array $head;
  public $tail;

  /**
   * @param bool                            $extern
   * @param SuperName[]                     $super
   * @param UpperName[]                     $head
   * @param StarSegment|LowerName|UpperName $tail
   */
  public function __construct(bool $extern, array $super, array $head, $tail) {
    parent::__construct();
    $this->is_extern = $extern;
    $this->super     = $super;
    $this->head      = $head;
    $this->tail      = $tail;
  }

  public function children(): array {
    return array_merge($this->super, $this->head, [ $this->tail ]);
  }
}
