<?php

namespace Cthulhu\AST;

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

  public function returns(): bool {
    if ($this->empty()) {
      return false;
    }

    return $this->last_stmt() instanceof ExprStmt;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('BlockNode', $visitor_table)) {
      $visitor_table['BlockNode']($this);
    }

    foreach ($this->stmts as $stmt) {
      $stmt->visit($visitor_table);
    }
  }

  public function jsonSerialize() {
    return array_map(function ($stmt) {
      return $stmt->jsonSerialize();
    }, $this->stmts);
  }
}
