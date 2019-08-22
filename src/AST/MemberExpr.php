<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class MemberExpr extends Expr {
  public $object;
  public $property;

  function __construct(Span $span, Expr $object, string $property) {
    parent::__construct($span);
    $this->object = $object;
    $this->property = $property;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('MemberExpr', $visitor_table)) {
      $visitor_table['MemberExpr']($this);
    }

    $this->object->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'MemberExpr',
      'object' => $this->object->jsonSerialize(),
      'property' => $this->property
    ];
  }
}
