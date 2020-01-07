<?php

namespace Cthulhu\loc;

interface Spanlike {
  public function span(): Span;

  public function from(): Point;

  public function to(): Point;
}
