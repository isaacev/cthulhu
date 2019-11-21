<?php

namespace Cthulhu\ir\nodes;

class UnitVariantDeclNode extends VariantDeclNode {
  function children(): array {
    return [ $this->name ];
  }
}
