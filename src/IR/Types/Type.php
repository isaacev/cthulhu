<?php

namespace Cthulhu\IR\Types;

use Cthulhu\IR;

abstract class Type {
  protected $binops = [];

  function add_binop(string $op, self $rhs, self $ret): void {
    if (array_key_exists($op, $this->binops)) {
      $this->binops[$op][] = [$rhs, $ret];
    } else {
      $this->binops[$op] = [ [$rhs, $ret] ];
    }
  }

  function get_binop(string $op, self $rhs): ?self {
    if (array_key_exists($op, $this->binops)) {
      foreach ($this->binops[$op] as $tuple) {
        if ($tuple[0]->equals($rhs)) {
          return $tuple[1];
        }
      }
    }
    return null;
  }

  abstract function equals(self $other): bool;
  abstract function __toString(): string;
}
