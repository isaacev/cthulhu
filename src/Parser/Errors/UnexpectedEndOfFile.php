<?php

namespace Cthulhu\Parser\Errors;

class UnexpectedEndOfFile extends \Exception {
  function __construct(?string $wanted_type = null) {
    if ($wanted_type !== null) {
      parent::__construct("unexpected end of file, wanted $wanted_type");
    } else {
      parent::__construct("unexpected end of file");
    }
  }
}
