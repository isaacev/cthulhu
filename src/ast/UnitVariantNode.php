<?php

namespace Cthulhu\ast;

class UnitVariantNode extends VariantNode {
  function __construct(UpperNameNode $name) {
    parent::__construct($name->span, $name);
  }
}
