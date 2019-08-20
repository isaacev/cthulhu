<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;

class VarExpr extends Expr {
  public $name;

  function __construct(string $name) {
    $this->name = $name;
  }

  public function precedence(): int {
    return PHP_INT_MAX;
  }

  public function build(): Builder {
    return (new Builder)
      ->variable($this->name);
  }

  public function jsonSerialize() {
    return [
      'type' => 'VarExpr',
      'name' => $this->name
    ];
  }
}
