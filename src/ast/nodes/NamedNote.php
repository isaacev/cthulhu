<?php

namespace Cthulhu\ast\nodes;

class NamedNote extends Note {
  public PathNode $path;

  public function __construct(PathNode $path) {
    parent::__construct();
    $this->path = $path;
  }

  public function children(): array {
    return [ $this->path ];
  }
}
