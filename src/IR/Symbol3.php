<?php

namespace Cthulhu\IR;

class Symbol3 implements \JsonSerializable {
  private static $next_uid = 1;

  public $id;
  public $name;
  public $parent;

  function __construct(string $name, ?self $parent = null) {
    $this->id = strval(self::$next_uid++);
    $this->name = $name;
    $this->parent = $parent;
  }

  public function jsonSerialize() {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'parent' => $this->parent
    ];
  }

  public function __toString(): string {
    if ($this->parent) {
      return $this->parent->__toString() . '::' . $this->name;
    } else {
      return $this->name;
    }
  }
}
