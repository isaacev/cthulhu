<?php

namespace Cthulhu\val;

use Exception;

class UnknownEscapeChar extends Exception {
  public int $char_offset;

  public function __construct(int $char_offset) {
    parent::__construct("unknown escape character at position $char_offset");
    $this->char_offset = $char_offset;
  }
}
