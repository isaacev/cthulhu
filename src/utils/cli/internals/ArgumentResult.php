<?php

namespace Cthulhu\utils\cli\internals;

class ArgumentResult {
  public $id;
  public $value;

  function __construct(string $id, $value) {
    $this->id = $id;
    $this->value = $value;
  }
}