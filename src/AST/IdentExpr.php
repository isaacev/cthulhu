<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class IdentExpr extends Expr {
  public $name;

  function __construct(Span $span, string $name) {
    parent::__construct($span);
    $this->name = $name;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('IdentExpr', $visitor_table)) {
      $visitor_table['IdentExpr']($this);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'IdentExpr',
      'name' => $this->name
    ];
  }
}
