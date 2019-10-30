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

  function accepts_as_parameter(Type $other): bool {
    if (self::does_not_match($other)) {
      return false;
    }

    if (count($this->inputs) !== count($other->inputs)) {
      return false;
    }

    foreach (array_map(null, $this->inputs, $other->inputs) as list($p1, $p2)) {
      if ($p1->accepts($p2) === false) {
        return false;
      }
    }

    return $this->output->accepts_as_parameter($other->output);
  }

  function unify(Type $other): ?Type {
    if (($other instanceof self) === false) {
      return null;
    }

    if (count($this->inputs) !== count($other->inputs)) {
      return null;
    }

    $new_inputs = [];
    foreach (array_map(null, $this->inputs, $other->inputs) as list($p1, $p2)) {
      if ($unification = $p1->unify($p2)) {
        $new_inputs[] = $unification;
      } else {
        return null;
      }
    }

    $new_output = $this->output->unify($other->output);
    if (!$new_output) {
      return null;
    }

    return new self($new_inputs, $new_output);
  }

  function __toString(): string {
    if (empty($this->inputs)) {
      $inputs = '()';
    } else if (count($this->inputs) === 1 && !($this->inputs[0] instanceof TupleType)) {
      $inputs = (string)$this->inputs[0];
    } else {
      $inputs = '(' . implode(', ', $this->inputs) . ')';
    }
    return $inputs . " -> $this->output";
  }

  static function matches(Type $other): bool {
    return $other instanceof self;
  }

  static function does_not_match(Type $other): bool {
    return self::matches($other) === false;
  }
}
