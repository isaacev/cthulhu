<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class VariantPattern extends Pattern {
  public PathNode $path;
  public ?VariantPatternFields $fields;

  public function __construct(Span $span, PathNode $path, ?VariantPatternFields $fields) {
    parent::__construct($span);
    $this->path   = $path;
    $this->fields = $fields;
  }
}
