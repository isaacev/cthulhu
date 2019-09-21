<?php

namespace Cthulhu\IR;

use Cthulhu\Source;

class Symbol implements \JsonSerializable {
  private static $next_uid = 1;

  public $id;
  public $name;
  public $origin;
  public $parent;

  function __construct(string $name, ?Source\Span $origin, ?self $parent = null) {
    $this->id = strval(self::$next_uid++);
    $this->name = $name;
    $this->origin = $origin;
    $this->parent = $parent;
  }

  public function jsonSerialize() {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'origin' => $this->origin,
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
