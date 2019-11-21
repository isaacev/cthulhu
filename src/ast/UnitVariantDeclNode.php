<?php

namespace Cthulhu\ast;

class UnitVariantDeclNode extends VariantDeclNode {
  function __construct(UpperNameNode $name) {
    parent::__construct($name->span, $name);
  }
}
