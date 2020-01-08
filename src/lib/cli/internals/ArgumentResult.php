<?php

namespace Cthulhu\lib\cli\internals;

class ArgumentResult {
  public string $id;
  public $value;

  /**
   * @param string $id
   * @param mixed  $value
   */
  public function __construct(string $id, $value) {
    $this->id    = $id;
    $this->value = $value;
  }
}
