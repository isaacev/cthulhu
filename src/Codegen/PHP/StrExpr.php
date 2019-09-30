<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class StrExpr extends Expr {
  public $value;

  function __construct(string $value) {
    $this->value = $value;
  }

  public function build(): Builder {
    return (new Builder)
      ->string_literal($this->value);
  }

  public function jsonSerialize() {
    return [
      'type' => 'StrExpr',
      'value' => $this->value
    ];
  }
}
