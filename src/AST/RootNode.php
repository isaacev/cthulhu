<?php

namespace Cthulhu\AST;

class RootNode extends Node {
  public $block;

  function __construct(BlockNode $block) {
    $this->block = $block;
  }

  public function jsonSerialize() {
    return [
      'type' => 'RootNode',
      'stmts' => $this->block->jsonSerialize()
    ];
  }
}
