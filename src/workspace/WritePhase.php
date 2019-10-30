<?php

namespace Cthulhu\workspace;

use Cthulhu\lib\fmt\StringFormatter;
use Cthulhu\php\nodes\Program;

class WritePhase {
  private $php_tree;

  function __construct(Program $php_tree) {
    $this->php_tree = $php_tree;
  }

  function write(): string {
    return $this->php_tree->build()->write(new StringFormatter());
  }
}
