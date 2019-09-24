<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Buildable;

abstract class Node implements Buildable {
  function visit(array $table): void {
    if (array_key_exists('Node', $table)) {
      $table['Node']($this);
    }
  }
}
