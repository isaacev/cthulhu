<?php

namespace Cthulhu\ast\nodes;

class PathNode extends Node {
  public bool $is_extern;
  public array $head;
  public Name $tail;

  /**
   * @param bool        $extern
   * @param UpperName[] $head
   * @param Name        $tail
   */
  public function __construct(bool $extern, array $head, Name $tail) {
    parent::__construct();
    $this->is_extern = $extern;
    $this->head      = $head;
    $this->tail      = $tail;
  }

  public function children(): array {
    return array_merge($this->head, [ $this->tail ]);
  }
}
