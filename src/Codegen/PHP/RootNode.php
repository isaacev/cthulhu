<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class RootNode extends Node {
  public $block;

  function __construct(BlockNode $block) {
    $this->block = $block;
  }

  public function build(): Builder {
    return (new Builder)
      ->opening_php_tag()
      ->stmts($this->block->stmts);
  }

  public function jsonSerialize() {
    return [
      'type' => 'RootNode'
    ];
  }
}
