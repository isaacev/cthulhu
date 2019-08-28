<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\{ Buildable, Builder };

class Program implements Buildable {
  public $builtins;
  public $modules;

  function __construct(array $builtins, array $modules) {
    $this->builtins = $builtins;
    $this->modules = $modules;
  }

  public function build(): Builder {
    return (new Builder)
      ->opening_php_tag()
      ->each($this->builtins)
      ->each($this->modules);
  }
}
