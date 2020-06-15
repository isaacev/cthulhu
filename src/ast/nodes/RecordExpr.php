<?php

namespace Cthulhu\ast\nodes;

class RecordExpr extends Expr {
  public array $fields;

  /**
   * @param FieldExprNode[] $fields
   */
  public function __construct(array $fields) {
    parent::__construct();
    $this->fields = $fields;
  }

  public function children(): array {
    return $this->fields;
  }
}
