<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;

class IdentifierPath implements Buildable {
  public $segments;

  function __construct(array $segments) {
    $this->segments = $segments;
  }

  public function length(): int {
    return count($this->segments);
  }

  public function build(): Builder {
    return (new Builder)
      ->each($this->segments, (new Builder)->backslash());
  }
}
