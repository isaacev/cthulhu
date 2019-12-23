<?php

namespace Cthulhu\ir\types;

use Cthulhu\ir\nodes\Ref;

class NamedType extends Type {
  use traits\DefaultWalkable;

  public Ref $ref;
  public array $params;
  public Type $pointer;

  /**
   * @param Ref    $ref
   * @param Type[] $params
   * @param Type   $pointer
   */
  public function __construct(Ref $ref, array $params, Type $pointer) {
    $this->ref     = $ref;
    $this->params  = $params;
    $this->pointer = $pointer;
  }

  public function similar_to(Walkable $other): bool {
    return $other instanceof self;
  }

  public function equals(Type $other): bool {
    if ($other instanceof NamedType && $this->ref->equals($other->ref)) {
      assert(count($this->params) === count($other->params));
      for ($i = 0; $i < count($this->params); $i++) {
        $this_param  = $this->params[$i];
        $other_param = $other->params[$i];
        if ($this_param->equals($other_param) === false) {
          return false;
        }
      }
      return $this->pointer->equals($other->pointer);
    }
    return false;
  }

  public function __toString(): string {
    if (empty($this->params)) {
      return "$this->ref";
    }
    return "$this->ref(" . implode(", ", $this->params) . ")";
  }

  /**
   * @return Type[]
   */
  private function to_children(): array {
    return [ $this->pointer, ...$this->params ];
  }

  /**
   * @param Type[] $children
   * @return NamedType
   */
  private function from_children(array $children): NamedType {
    return new NamedType($this->ref, array_slice($children, 1), $children[0]);
  }

  /**
   * @param Type[] $new_params
   * @return NamedType
   */
  public function bind_free_types(array $new_params): NamedType {
    assert(count($this->params) === count($new_params));

    $reps = [];
    foreach ($this->params as $index => $old_param) {
      $new_param = $new_params[$index];
      if ($old_param instanceof FreeType) {
        $reps[$old_param->symbol->get_id()] = $new_param;
      } else {
        $new_params[$index] = $old_param;
      }
    }

    $new_pointer = $this->pointer->transform(function (Type $type) use ($reps): ?Type {
      if ($type instanceof FreeType) {
        $free_id = $type->symbol->get_id();
        if ($rep = @$reps[$free_id]) {
          return $rep;
        }
      }
      return null;
    });

    return new NamedType($this->ref, $new_params, $new_pointer ?? $this->pointer);
  }
}
