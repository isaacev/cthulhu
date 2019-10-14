<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class NamespaceNode extends Node {
  public $name;
  public $block;

  function __construct(?Reference $name, BlockNode $block) {
    $this->name = $name;
    $this->block = $block;
  }

  public function to_children(): array {
    return [ $this->block ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->name, $nodes[0]);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('namespace')
      ->space()
      ->then($this->name
        ? (new Builder)->then($this->name)->space()
        : (new Builder))
      ->then($this->block);
  }
}
