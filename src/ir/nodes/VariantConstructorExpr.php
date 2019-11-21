<?php

namespace Cthulhu\ir\nodes;

class VariantConstructorExpr extends Expr {
  public $ref;
  public $fields;

  function __construct(Ref $ref, ?VariantConstructorFields $fields) {
    parent::__construct();
    $this->ref = $ref;
    $this->fields = $fields;
  }

  public function children(): array {
    return [
      $this->ref,
      $this->fields,
    ];
  }
}
