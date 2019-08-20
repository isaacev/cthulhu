<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class BlockNode extends Node {
  public $stmts;

  function __construct(Span $span, array $stmts) {
    parent::__construct($span);
    $this->stmts = $stmts;
  }

  public function jsonSerialize() {
    return array_map(function ($stmt) {
      return $stmt->jsonSerialize();
    }, $this->stmts);
  }
}
