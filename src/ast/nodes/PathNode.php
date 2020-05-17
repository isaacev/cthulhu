<?php

namespace Cthulhu\ast\nodes;

class PathNode extends Node {
  public bool $is_extern;
  public array $super;
  public array $head;
  public Name $tail;

  /**
   * @param bool        $extern
   * @param SuperName[] $super
   * @param UpperName[] $head
   * @param Name        $tail
   */
  public function __construct(bool $extern, array $super, array $head, Name $tail) {
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
