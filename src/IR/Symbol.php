<?php

namespace Cthulhu\IR;

class Symbol implements \JsonSerializable {
  public $id;
  public $scope;
  private static $next_uid = 1;

  function __construct(?Scope $scope) {
    $this->id = strval(Symbol::$next_uid++);
    $this->scope = $scope;
  }

  public function equals(Symbol $other): bool {
    return $this->id === $other->id;
  }

  public function jsonSerialize() {
    return $this->id;
  }
}
