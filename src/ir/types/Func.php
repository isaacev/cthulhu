<?php

namespace Cthulhu\ir\types;

class Func extends ConcreteType {
  public Type $input;
  public Type $output;

  public function __construct(Type $input, Type $output) {
    parent::__construct('Func', [ $input, $output ]);
    $this->input  = $input;
    $this->output = $output;
  }

  public function fresh(ParameterContext $ctx): Type {
    return new Func(
      $this->input->fresh($ctx),
      $this->output->fresh($ctx));
  }

  public function advance(int $total_arguments): ?Type {
    assert($total_arguments >= 0);
    if ($total_arguments === 0) {
      return $this;
    } else if ($total_arguments === 1) {
      return $this->output;
    } else if ($this->output instanceof Func) {
      return $this->output->advance($total_arguments - 1);
    } else {
      return null;
    }
  }

  public function __toString(): string {
    if ($this->input->prune() instanceof Func) {
      return "($this->input) -> $this->output";
    } else {
      return "$this->input -> $this->output";
    }
  }

  /**
   * @param Type[] $inputs
   * @param Type   $output
   * @return Func
   */
  public static function from_input_array(array $inputs, Type $output): Func {
    if (empty($inputs)) {
      return new Func(Atomic::unit(), $output);
    }

    foreach (array_reverse($inputs) as $input) {
      $output = new Func($input, $output);
    }

    return $output;
  }
}
