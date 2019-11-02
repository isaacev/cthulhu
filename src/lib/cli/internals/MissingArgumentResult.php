<?php

namespace Cthulhu\lib\cli\internals;

class MissingArgumentResult extends ArgumentResult {
  function __construct(string $id) {
    parent::__construct($id, null);
  }
}
