<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class NullLiteral extends Expr {
  use Traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->null_literal();
  }

  public function jsonSerialize() {
    return [
      'type' => 'NullLiteral'
    ];
  }
}
