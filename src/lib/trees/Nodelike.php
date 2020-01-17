<?php

namespace Cthulhu\lib\trees;

interface Nodelike {
  public function get_id(): int;

  /**
   * @return Nodelike[]
   */
  public function children(): array;
}
