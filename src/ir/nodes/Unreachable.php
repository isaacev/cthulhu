<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class Unreachable extends Expr {
  public int $line;
  public string $file;

  public function __construct(Type $type, int $line, string $file) {
    parent::__construct($type);
    $this->line = $line;
    $this->file = $file;
  }

  public function children(): array {
    return [];
  }

  public function from_children(array $children): Node {
    return $this;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('unreachable');
  }
}
