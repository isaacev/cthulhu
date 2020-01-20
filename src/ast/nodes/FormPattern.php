<?php

namespace Cthulhu\ast\nodes;

abstract class FormPattern extends Pattern {
  public PathNode $path;

  public function __construct(PathNode $path) {
    parent::__construct();
    $this->path = $path;
  }
}
