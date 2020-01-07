<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class BlockNode extends Node {
  public array $stmts;

  public function __construct(Span $span, array $stmts) {
    parent::__construct($span);
    $this->stmts = $stmts;
  }

  public function empty(): bool {
    return empty($this->stmts);
  }

  public function last_stmt(): ?Stmt {
    if ($this->empty()) {
      return null;
    } else {
      return end($this->stmts);
    }
  }
}
