<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class Program extends Node {
  public array $namespaces;

  /**
   * @param NamespaceNode[] $namespaces
   */
  public function __construct(array $namespaces) {
    parent::__construct();
    $this->namespaces = $namespaces;
  }

  public function to_children(): array {
    return $this->namespaces;
  }

  public function from_children(array $namespaces): Node {
    return new self($namespaces);
  }

  public function build(): Builder {
    return (new Builder)
      ->opening_php_tag()
      ->newline()
      ->each($this->namespaces, (new Builder)
        ->newline()
        ->newline())
      ->newline();
  }
}
