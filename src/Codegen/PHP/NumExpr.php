<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class NumExpr extends Expr {
  public $value;

  function __construct(int $value) {
    $this->value = $value;
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('NumExpr', $table)) {
      $table['NumExpr']($this);
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->int_literal($this->value);
  }

  public function jsonSerialize() {
    return [
      'type' => 'NumExpr',
      'value' => $this->value
    ];
  }
}
