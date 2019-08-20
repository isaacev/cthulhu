<?php

namespace Cthulhu\Types\Errors;

class UnknownType extends \Cthulhu\Errors\TypeError {
  function __construct(string $name) {
    parent::__construct("unknown type: $name");
  }
}
