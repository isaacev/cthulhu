<?php

namespace Cthulhu\ir\nodes;

class UnitVariantConstructor extends VariantConstructor {
  function children(): array {
    return [ $this->ref ];
  }
}
