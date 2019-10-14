<?php

namespace Cthulhu\ir;

trait GenerateId {
  private static $next_id = 1;
  protected $id;

  function __construct() {
    $this->id = self::$next_id++;
  }

  public function get_id(): int {
    return $this->id;
  }
}
