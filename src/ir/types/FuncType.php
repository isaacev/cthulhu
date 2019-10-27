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

    return $this->output->accepts($other->output);
  }

  function unify(Type $other): ?Type {
    if (($other instanceof self) === false) {
      return null;
    }

    if (count($this->polys) !== count($other->polys)) {
      return null;
    }

    $new_polys = [];
    foreach (array_map(null, $this->polys, $other->polys) as list($p1, $p2)) {
      if ($unification = $p1->unify($p2)) {
        $new_polys[] = $unification;
      } else {
        return null;
      }
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

    return new self($new_polys, $new_inputs, $new_output);
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
