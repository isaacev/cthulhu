<?php

namespace Cthulhu\ir;

trait GenerateId {
  private static int $next_id = 1;
  protected int $id;

  public function __construct() {
    $this->id = self::$next_id++;
  }

  public function get_id(): int {
    return $this->id;
  }
}
