<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class NullLiteral extends Literal {
  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->null_literal();
  }
}
