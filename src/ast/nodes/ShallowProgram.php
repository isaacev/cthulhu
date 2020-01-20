<?php

namespace Cthulhu\ast\nodes;

class ShallowProgram extends ShallowNode {
  public array $files;

  /**
   * @param ShallowFile[] $files
   */
  public function __construct(array $files) {
    parent::__construct();
    $this->files = $files;
  }

  public function children(): array {
    return $this->files;
  }
}
