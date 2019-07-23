<?php

namespace Cthulhu\Types\Errors;

use Cthulhu\Types\Type;

class TypeMismatch extends \Exception {
  function __construct(Type $wanted, Type $found) {
    parent::__construct("wanted $wanted but found $found");
  }
}
