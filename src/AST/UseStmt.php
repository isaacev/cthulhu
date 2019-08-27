<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class UseStmt extends Stmt {
  public $segment;

  function __construct(Span $span, IdentNode $segment) {
    parent::__construct($span);
    $this->segment = $segment;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('UseStmt', $visitor_table)) {
      $visitor_table['UseStmt']($this);
    }

    $this->segment->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'UseStmt',
      'segment' => $this->segment
    ];
  }
}
