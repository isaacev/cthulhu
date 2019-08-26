<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class NamespaceNode extends Node {
  public $path;
  public $block;

  function __construct(IdentifierPath $path, BlockNode $block) {
    $this->path = $path;
    $this->block = $block;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('namespace')
      ->space()
      ->choose($this->path->length() === 0,
        (new Builder),
        (new Builder)
          ->then($this->path)
          ->space())
      ->then($this->block);
  }
}
