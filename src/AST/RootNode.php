<?php

namespace Cthulhu\AST;

class RootNode extends Node {
  public $block;

  function __construct(BlockNode $block) {
    $this->block = $block;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('RootNode', $visitor_table)) {
      $visitor_table['RootNode']($this);
    }

    $this->block->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'RootNode',
      'stmts' => $this->block->jsonSerialize()
    ];
  }
}
