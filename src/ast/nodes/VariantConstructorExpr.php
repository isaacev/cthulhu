<?php

namespace Cthulhu\ast\nodes;

class VariantConstructorExpr extends Expr {
  public PathNode $path;
  public ?VariantConstructorFields $fields;

  public function __construct(PathNode $path, ?VariantConstructorFields $fields) {
    parent::__construct();
    $this->path   = $path;
    $this->fields = $fields;
  }

  public function children(): array {
    return [ $this->path, $this->fields ];
  }
}
