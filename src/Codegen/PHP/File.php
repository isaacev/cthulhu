<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;
use Cthulhu\Codegen\Builder;

class File implements Buildable {
  public $modules;

  function __construct(array $modules) {
    $this->modules = $modules;
  }

  public function build(): Builder {
    return (new Builder)
      ->opening_php_tag()
      ->each($this->modules, (new Builder)->newline());
  }
}
