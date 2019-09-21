<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class LetStmt extends Stmt {
  public $name;
  public $expr;

  function __construct(Source\Span $span, IdentNode $name, Expr $expr) {
    parent::__construct($span);
    $this->name = $name;
    $this->expr = $expr;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('LetStmt', $visitor_table)) {
      $visitor_table['LetStmt']($this);
    }

    $this->name->visit($visitor_table);
    $this->expr->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'LetStmt',
      'name' => $this->name,
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
