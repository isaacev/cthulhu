<?php

namespace Cthulhu\Types;

class NumType extends Type {
  public function accepts(Type $other): bool {
    if ($other instanceof NumType) {
      return true;
    } else {
      return false;
    }
  }

  public function binary_operator(string $operator, Type $right): Type {
    if ($right instanceof NumType) {
      switch ($operator) {
        case '+':
        case '-':
        case '*':
        case '/':
          return new NumType();
        case '<':
        case '<=':
        case '>':
        case '>=':
          return new BoolType();
      }
    }
    return parent::binary_operator($operator, $right);
  }

  public function __toString(): string {
    return 'Num';
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumType'
    ];
  }
}
