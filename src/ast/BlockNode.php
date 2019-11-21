<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class BlockNode extends Node {
  public $stmts;

  function __construct(Source\Span $span, array $stmts) {
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
