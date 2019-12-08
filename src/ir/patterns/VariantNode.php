<?php

namespace Cthulhu\ir\patterns;

abstract class VariantNode extends Node {
  protected string $name;

  function __construct(string $name) {
    $this->name = $name;
  }
}
