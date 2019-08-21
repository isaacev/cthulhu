<?php

namespace Cthulhu\Types\Errors;

use Cthulhu\Types\Type;

class UnsupportedMember extends \Cthulhu\Errors\TypeError {
  function __construct(Type $object, string $property) {
    parent::__construct("no property '$property' on type $object");
  }
}
