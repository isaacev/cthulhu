<?php

namespace Cthulhu\lib\cli\internals;

class FlagResult {
  public string $id;
  public $value;

  /**
   * @param string $id
   * @param mixed  $value
   */
  function __construct(string $id, $value) {
    $this->id    = $id;
    $this->value = $value;
  }
}
