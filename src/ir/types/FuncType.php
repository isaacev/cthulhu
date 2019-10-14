<?php

namespace Cthulhu\ir\types;

class FuncType extends Type {
  public $inputs;
  public $output;

  /**
   * @param Type[] $inputs
   * @param Type   $output
   */
  function __construct(array $polys, array $inputs, Type $output) {
    $this->polys  = $polys;
    $this->inputs = $inputs;
    $this->output = $output;
  }

  function accepts(Type $other): bool {
    return $this->equals($other);
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

  function replace_generics(array $replacements): Type {
    $inputs = array_map(function ($input) use ($replacements) {
      return $input->replace_generics($replacements);
    }, $this->inputs);
    $output = $this->output->replace_generics($replacements);
    return new self([], $inputs, $output);
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
