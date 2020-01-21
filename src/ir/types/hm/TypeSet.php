<?php

namespace Cthulhu\ir\types\hm;

/**
 * A set (in the math sense) of `hm\TypeVar`s
 */
class TypeSet {
  private array $set = [];

  public function __construct(?self $extends = null) {
    if ($extends) {
      $this->set = $extends->set;
    }
  }

  public function add(TypeVar $v): void {
    $this->set[$v->id] = $v;
  }

  public function has(TypeVar $v): bool {
    return array_key_exists($v->id, $this->set);
  }
}
