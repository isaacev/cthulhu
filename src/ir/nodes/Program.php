<?php

namespace Cthulhu\ir\nodes;

class Program extends Node {
  public array $libs;

  /**
   * @param Library[] $libs
   */
  function __construct(array $libs) {
    parent::__construct();
    $this->libs = $libs;
  }

  function children(): array {
    return $this->libs;
  }
}
