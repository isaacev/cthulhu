<?php

namespace Cthulhu\lib\cli\internals;

class MissingArgumentResult extends ArgumentResult {
  public function __construct(string $id) {
    parent::__construct($id, null);
  }
}
