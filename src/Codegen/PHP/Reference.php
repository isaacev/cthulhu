<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;
use Cthulhu\IR;

class Reference implements Buildable {
  public static function from_symbol(IR\Symbol3 $symbol): Reference {
    $segments = [];
    while ($symbol !== null) {
      $segments[] = $symbol->name;
      $symbol = $symbol->parent;
    }
    return new Reference(array_reverse($segments));
  }

  public $segments;

  function __construct(array $segments) {
    $this->segments = $segments;
  }

  public function build(): Builder {
    return (new Builder)
      ->reference($this->segments);
  }
}
