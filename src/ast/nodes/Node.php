<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Point;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

abstract class Node implements Spanlike {
  public Span $span;

  public function __construct(Span $span) {
    $this->span = $span;
  }

  public function span(): Span {
    return $this->span;
  }

  public function from(): Point {
    return $this->span->from;
  }

  public function to(): Point {
    return $this->span->to;
  }
}
