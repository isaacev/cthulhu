<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class NamespaceNode extends Node {
  public $name;
  public $block;

  function __construct(Reference $name, BlockNode $block) {
    $this->name = $name;
    $this->block = $block;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('namespace')
      ->space()
      ->then($this->name)
      ->space()
      ->then($this->block);
  }
}
