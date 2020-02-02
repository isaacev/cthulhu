<?php

namespace Cthulhu\php\nodes\traits;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\nodes;

trait Unary {
  public function children(): array {
    return [ $this->expr ];
  }

  public function from_children(array $nodes): nodes\Node {
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return new self($nodes[0], $this->next);
  }

  public function from_successor(?EditableSuccessor $next): EditableSuccessor {
    /** @noinspection PhpMethodParametersCountMismatchInspection */
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return new self($this->expr, $next);
  }
}
