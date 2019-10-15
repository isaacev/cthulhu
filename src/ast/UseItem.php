<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class UseItem extends Item {
  public $path;

  function __construct(Source\Span $span, CompoundPathNode $path, array $attrs) {
    parent::__construct($span, $attrs);
    $this->path = $path;
  }
}
