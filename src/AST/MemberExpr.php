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

  public function jsonSerialize() {
    return [
      'type' => 'MemberExpr',
      'object' => $this->object->jsonSerialize(),
      'property' => $this->property
    ];
  }
}
