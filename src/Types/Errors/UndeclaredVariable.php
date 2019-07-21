<?php

namespace Cthulhu\Types\Errors;

class UndeclaredVariable extends \Exception {
  function __construct(string $variable_name) {
    parent::__construct("use of undeclared variable '$variable_name'");
  }
}
