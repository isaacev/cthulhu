<?php

namespace Cthulhu\ast\nodes;

class Program extends Node {
  public array $files;

  /**
   * @param File[] $files
   */
  public function __construct(array $files) {
    parent::__construct();
    $this->files = $files;
  }

  public function children(): array {
    return $this->files;
  }
}
