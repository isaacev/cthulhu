<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class NullLiteral extends Expr {
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
