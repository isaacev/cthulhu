<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class NamespaceNode extends Stmt {
  public $name;
  public $block;

  function __construct(Reference $name, BlockNode $block) {
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
      ->then($this->name)
      ->space()
      ->then($this->block);
  }
}
