<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\lib\fmt\Buildable;
use Cthulhu\lib\trees\DefaultMetadata;
use Cthulhu\lib\trees\HasMetadata;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

abstract class Expr implements HasMetadata, Buildable {
  use DefaultMetadata;

  public Span $span;

  public function __construct(Spanlike $spanlike) {
    $this->span = $spanlike->span();
  }
}
