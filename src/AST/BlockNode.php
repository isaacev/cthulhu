<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class BlockNode extends Node {
  public $stmts;

  function __construct(Span $span, array $stmts) {
    parent::__construct($span);
    $this->stmts = $stmts;
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
