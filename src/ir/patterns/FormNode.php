<?php

namespace Cthulhu\ir\patterns;

abstract class FormNode extends Node {
  protected string $name;

  public function __construct(string $name) {
    $this->name = $name;
  }
}
