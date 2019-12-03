<?php

namespace Cthulhu\ir\nodes;

class CompoundRef extends Node {
  public bool $extern;
  public array $body;
  public $tail;

  /**
   * @param bool         $extern
   * @param Name[]       $body
   * @param StarRef|Name $tail
   */
  function __construct(bool $extern, array $body, $tail) {
    parent::__construct();
    $this->extern = $extern;
    $this->body   = $body;
    $this->tail   = $tail;
  }

  function children(): array {
    return array_merge(
      $this->body,
      [ $this->tail ]
    );
  }
}
