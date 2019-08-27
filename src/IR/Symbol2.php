<?php

namespace Cthulhu\IR;

class Symbol2 implements \JsonSerializable {
  private static $next_uid = 1;

  public $id;
  public $scope;
  public $name;

  function __construct(?Scope2 $scope, string $name) {
    $this->id = strval(Symbol2::$next_uid++);
    $this->scope = $scope;
    $this->name = $name;
  }

  public function jsonSerialize() {
    return $this->id;
  }
}
