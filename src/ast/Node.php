<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

abstract class Node {
  public $span;

  function __construct(Source\Span $span) {
    $this->span = $span;
  }
}
