<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class StrLiteral extends Literal {
  public $value;

  function __construct(string $value) {
    parent::__construct();
    $this->value = $value;
  }

  use traits\Atomic;

  public function build(): Builder {
    return (new Builder)
      ->string_literal($this->value);
  }
}
