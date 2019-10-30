<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NativeTypeItem extends Item {
  public $name;

  function __construct(Source\Span $span, UpperNameNode $name, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
  }
}
