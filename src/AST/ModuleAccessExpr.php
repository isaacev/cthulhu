<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class ModuleAccessExpr extends Expr {
  public $segments;

  function __construct(Span $span, array $segments) {
    parent::__construct($span);
    $this->segments = $segments;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('ModuleAccessExpr', $visitor_table)) {
      $visitor_table['ModuleAccessExpr']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'ModuleAccessExpr',
      'segments' => $this->segments
    ];
  }
}
