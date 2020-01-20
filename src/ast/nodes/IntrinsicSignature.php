<?php

namespace Cthulhu\ast\nodes;

class IntrinsicSignature extends Node {
  public LowerName $name;
  public Note $params;
  public Note $returns;

  /**
   * @param LowerName $name
   * @param Note      $params
   * @param Note      $returns
   */
  public function __construct(LowerName $name, Note $params, Note $returns) {
    parent::__construct();
    $this->name    = $name;
    $this->params  = $params;
    $this->returns = $returns;
  }

  public function children(): array {
    return [ $this->name, $this->params, $this->returns ];
  }
}
