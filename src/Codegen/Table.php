<?php

namespace Cthulhu\Codegen;

class Table {
  protected $callbacks;

  function __construct(array $callbacks) {
    $this->callbacks = $callbacks;
  }

  public function apply(Path $path) {
    $name = str_replace('Cthulhu\\Codegen\\PHP\\', '', get_class($path->node));
    if (array_key_exists($name, $this->callbacks)) {
      return $this->callbacks[$name]($path);
    }
    return null;
  }
}
