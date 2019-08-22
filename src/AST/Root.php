<?php

namespace Cthulhu\AST;

class Root implements \JsonSerializable {
  public $block;

  function __construct(BlockNode $block) {
    $this->block = $block;
  }

  public function jsonSerialize() {
    return [
      'type' => 'Root',
      'stmts' => $this->block->jsonSerialize()
    ];
  }
}
