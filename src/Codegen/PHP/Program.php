<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\{ Buildable, Builder };

class Program extends Node {
  public $modules;

  function __construct(array $modules) {
    $this->modules = $modules;
  }

  public function to_children(): array {
    return $this->modules;
  }

  public function from_children(array $nodes): Node {
    return new self($nodes);
  }

  public function build(): Builder {
    return (new Builder)
      ->opening_php_tag()
      ->newline()
      ->each($this->modules, (new Builder)
        ->newline()
        ->newline());
  }
}
