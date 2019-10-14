<?php

namespace Cthulhu\ir\types;

class FuncType extends Type {
  public $inputs;
  public $output;

  /**
   * @param Type[] $inputs
   * @param Type   $output
   */
  function __construct(array $inputs, Type $output) {
    $this->inputs = $inputs;
    $this->output = $output;
  }

  function equals(Type $other): bool {
    if (self::dooes_not_match($other)) {
      return false;
    }

    if (count($this->inputs) !== count($other->inputs)) {
      return false;
    }

    foreach (array_map(null, $this->inputs, $other->inputs) as list($p1, $p2)) {
      if ($p1->equals($p2) === false) {
        return false;
      }
    }

    return $this->output->equals($other->output);
  }

  function __toString(): string {
    $inputs = implode(', ', $this->inputs);
    return "($inputs) -> $this->output";
  }

  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
