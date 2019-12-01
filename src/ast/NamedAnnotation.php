<?php

namespace Cthulhu\ast;

class NamedAnnotation extends Annotation {
  public PathNode $path;

  function __construct(PathNode $path) {
    parent::__construct($path->span);
    $this->path = $path;
  }
}
