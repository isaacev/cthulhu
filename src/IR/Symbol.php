<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

class Symbol implements \JsonSerializable {
  public $id;
  public $type;
  public $scope;
  private static $next_uid = 1;

  function __construct(Type $type, Scope $scope) {
    $this->id = strval(Symbol::$next_uid++);
    $this->type = $type;
    $this->scope = $scope;
  }

  public function equals(Symbol $other): bool {
    if ($this === $other) {
      return true;
    } else if ($this->id === $other->id && $this->type->equals($other->type)) {
      return true;
    } else {
      return false;
    }
  }

  public function jsonSerialize() {
    return [
      'id' => $this->id,
      'type' => $this->type->jsonSerialize()
    ];
  }
}
