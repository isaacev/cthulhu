<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class BlockNode extends Node {
  public array $stmts;

  /**
   * @param Stmt[] $stmts
   */
  public function __construct(array $stmts) {
    parent::__construct();
    $this->stmts = $stmts;
  }

  public function is_empty(): bool {
    return count($this->stmts) === 0;
  }

  public function children(): array {
    return $this->stmts;
  }

  public function from_children(array $nodes): Node {
    return new self($nodes);
  }

  public function length(): int {
    return count($this->stmts);
  }

  public function build(): Builder {
    return (new Builder)
      ->block($this);
  }
}
