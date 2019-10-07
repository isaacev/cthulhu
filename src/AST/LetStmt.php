<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class LetStmt extends Stmt {
  public $name;
  public $note;
  public $expr;

  function __construct(Source\Span $span, IdentNode $name, ?Annotation $note, Expr $expr, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->note = $note;
    $this->expr = $expr;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('LetStmt', $visitor_table)) {
      $visitor_table['LetStmt']($this);
    }

    $this->name->visit($visitor_table);
    if ($this->note) {
      $this->note->visit($visitor_table);
    }
    $this->expr->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'LetStmt',
      'name' => $this->name,
      'note' => $this->note,
      'expr' => $this->expr->jsonSerialize()
    ];
  }
}
