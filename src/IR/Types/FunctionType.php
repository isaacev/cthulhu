<?php

namespace Cthulhu\IR\Types;

class FunctionType extends Type {
  public $inputs;
  public $output;

  function __construct(array $inputs, Type $output) {
    $this->inputs = $inputs;
    $this->output = $output;
  }

  private function equal_inputs(array $other_inputs): bool {
    if (count($this->inputs) != count($other_inputs)) {
      return false;
    }

    for ($i = 0; $i < count($this->inputs); $i++) {
      if ($this->inputs[$i]->equals($other_inputs[$i]) === false) {
        return false;
      }
    }

    return true;
  }

  function equals(Type $other): bool {
    return (
      $other instanceof self &&
      $this->output->equals($other->output) &&
      $this->equal_inputs($other->inputs)
    );
  }

  function __toString(): string {
    if (count($this->inputs) === 1) {
      return sprintf('%s -> %s', $this->inputs[0], $this->output);
    }
    return sprintf('(%s) -> %s', implode(', ', $this->inputs), $this->output);
  }

  static function is_equal_to(Type $other): bool {
    return $other instanceof self;
  }

  static function not_equal_to(Type $other): bool {
    return self::is_equal_to($other) === false;
  }
}
