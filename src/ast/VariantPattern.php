<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class VariantPattern extends Pattern {
  public PathNode $path;
  public ?VariantPatternFields $fields;

  function __construct(Source\Span $span, PathNode $path, ?VariantPatternFields $fields) {
    parent::__construct($span);
    $this->path   = $path;
    $this->fields = $fields;
  }
}
