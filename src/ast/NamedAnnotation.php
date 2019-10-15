<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NamedAnnotation extends Annotation {
  public $path;

  function __construct(PathNode $path) {
    parent::__construct($path->span);
    $this->path = $path;
  }
}
