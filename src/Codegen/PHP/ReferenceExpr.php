<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class ReferenceExpr extends Expr {
  public $reference;

  function __construct(Reference $reference) {
    $this->reference = $reference;
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('ReferenceExpr', $table)) {
      $table['ReferenceExpr']($this);
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->backslash()
      ->then($this->reference);
  }
}
