<?php

namespace Cthulhu\ir\types;

class FuncType extends Type {
  use traits\DefaultWalkable;
  use traits\StaticEquality;

  public Type $input;
  public Type $output;

  public function __construct(Type $input, Type $output) {
    $this->input  = $input;
    $this->output = $output;
  }

  public function arity(): int {
    if ($this->output instanceof self) {
      return 1 + $this->output->arity();
    }
    return 1;
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  public function equals(Type $other): bool {
    return (
      $other instanceof FuncType &&
      $this->input->equals($other->input) &&
      $this->output->equals($other->output)
    );
  }

  public function __toString(): string {
    if ($this->input instanceof FuncType) {
      return "($this->input) -> $this->output";
    }
    return "$this->input -> $this->output";
  }

  /**
   * @return Type[]
   */
  public function to_children(): array {
    return [ $this->input, $this->output ];
  }

  /**
   * @param Type[] $children
   * @return $this
   */
  public function from_children(array $children): FuncType {
    return new FuncType($children[0], $children[1]);
  }

  /**
   * @param Type[] $inputs
   * @param Type   $output
   * @return FuncType
   */
  public static function from_input_array(array $inputs, Type $output): FuncType {
    switch (count($inputs)) {
      case 0:
        return new FuncType(new UnitType(), $output);
      case 1:
        return new FuncType($inputs[0], $output);
      default:
        return new FuncType($inputs[0], FuncType::from_input_array(array_slice($inputs, 1), $output));
    }
  }
}
