<?php

namespace Cthulhu\ir\types;

interface TypeSupportingParameters {
  function total_parameters(): int;

  /**
   * @param Type[] $bindings
   * @return Type
   */
  function bind_parameters(array $bindings): Type;
}
