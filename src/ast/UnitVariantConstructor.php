<?php

namespace Cthulhu\ast;

class UnitVariantConstructor extends VariantConstructor {
  function __construct(PathNode $path) {
    parent::__construct($path->span, $path);
  }
}
