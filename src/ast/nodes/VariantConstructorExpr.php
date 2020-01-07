<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class VariantConstructorExpr extends Expr {
  public PathNode $path;
  public ?VariantConstructorFields $fields;

  public function __construct(Span $span, PathNode $path, ?VariantConstructorFields $fields) {
    parent::__construct($span);
    $this->path   = $path;
    $this->fields = $fields;
  }
}
