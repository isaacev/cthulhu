<?php

namespace Cthulhu\AST;

class MemberExpr extends Expr {
  public $object;
  public $property;

  function __construct(Expr $object, IdentExpr $property) {
    parent::__construct($object->span->extended_to($property->span));
    $this->object = $object;
    $this->property = $property;
  }

  public function jsonSerialize() {
    return [
      'type' => 'MemberExpr',
      'object' => $this->object->jsonSerialize(),
      'property' => $this->property->jsonSerialize()
    ];
  }
}
