<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class NumExpr extends Expr {
  public $value;

  function __construct(int $value) {
    $this->value = $value;
  }

  public function precedence(): int {
    return PHP_INT_MAX;
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
