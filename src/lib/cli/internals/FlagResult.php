<?php

namespace Cthulhu\lib\cli\internals;

class FlagResult {
  public $id;
  public $value;

  function __construct(string $id, $value) {
    $this->id = $id;
    $this->value = $value;
  }
}
