<?php

namespace Cthulhu\ir\nodes;

class UnitVariantNode extends VariantNode {
  function children(): array {
    return [ $this->name ];
  }
}
