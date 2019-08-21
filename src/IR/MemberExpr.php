<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class MemberExpr extends Expr {
  public $type;
  public $object;
  public $property;

  function __construct(Type $type, Expr $object, string $property) {
    $this->type = $type;
    $this->object = $object;
    $this->property = $property;
  }

  public function type(): Type {
    return $this->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'MemberExpr'
    ];
  }
}
