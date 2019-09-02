<?php

namespace Cthulhu\Debug;

class ReportOptions {
  protected $array;

  function __construct(array $array = []) {
    $this->array = $array;
  }

  public function get(string $name, mixed $default): mixed {
    if (array_key_exists($name, $this->array)) {
      return $this->array[$name];
    } else {
      return $default;
    }
  }
}
