<?php

namespace Cthulhu\ast\nodes;

class NamedAnnotation extends Annotation {
  public PathNode $path;

  public function __construct(PathNode $path) {
    parent::__construct($path->span);
    $this->path = $path;
  }
}
