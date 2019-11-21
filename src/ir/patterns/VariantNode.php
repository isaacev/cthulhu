<?php

namespace Cthulhu\ir\patterns;

abstract class VariantNode extends Node {
  protected $name;

  function __construct(string $name) {
    $this->name = $name;
  }
}
