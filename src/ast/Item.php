<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

abstract class Item extends Node {
  public $attrs;

  function __construct(Source\Span $span, array $attrs) {
    parent::__construct($span);
    $this->attrs = $attrs;
  }
}
