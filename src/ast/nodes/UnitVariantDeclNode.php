<?php

namespace Cthulhu\ast\nodes;

class UnitVariantDeclNode extends VariantDeclNode {
  public function __construct(UpperNameNode $name) {
    parent::__construct($name->span, $name);
  }
}
