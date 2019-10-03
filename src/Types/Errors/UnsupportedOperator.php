<?php

namespace Cthulhu\Types\Errors;

use Cthulhu\Types\Type;

class UnsupportedOperator extends \Cthulhu\Errors\TypeError {
  function __construct(Type $left, string $operator, ?Type $right = null) {
    if ($right) {
      parent::__construct("no operator for $left $operator $right");
    } else {
      parent::__construct("no operator for $operator $left");
    }
  }
}
