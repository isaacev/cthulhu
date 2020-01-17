<?php

namespace Cthulhu\lib\trees;

trait DefaultUniqueId {
  private static int $next_id = 1;
  protected int $id;

  public function __construct() {
    $this->id = self::$next_id++;
  }

  public function get_id(): int {
    return $this->id;
  }
}
