<?php

namespace Cthulhu\Types\Errors;

use Cthulhu\Types\Type;

class TypeMismatch extends \Cthulhu\Errors\TypeError {
  function __construct($wanted, $found) {
    parent::__construct("wanted $wanted but found $found");
  }
}
