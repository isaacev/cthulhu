<?php

namespace Cthulhu\ir\nodes;

class UnitVariantDeclNode extends VariantDeclNode {
  public function children(): array {
    return [ $this->name ];
  }
}
