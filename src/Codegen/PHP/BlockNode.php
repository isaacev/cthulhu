<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class BlockNode extends Node {
  public $stmts;

  function __construct(array $stmts) {
    $this->stmts = $stmts;
  }

  public function is_empty(): bool {
    return count($this->stmts) === 0;
  }

  public function build(): Builder {
    return (new Builder)
      ->indented_block($this);
  }

  public function jsonSerialize() {
    return [
      'type' => 'BlockNode'
    ];
  }
}
