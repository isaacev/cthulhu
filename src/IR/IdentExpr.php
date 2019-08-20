<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class IdentExpr extends Expr {
  public $type;
  public $name;

  function __construct(Type $type, string $name) {
    $this->type = $type;
    $this->name = $name;
  }

  public function type(): Type {
    return $this->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'IdentExpr',
      'type' => $this->type,
      'name' => $this->name
    ];
  }
}
