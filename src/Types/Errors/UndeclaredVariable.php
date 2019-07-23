<?php

namespace Cthulhu\Types\Errors;

class UndeclaredVariable extends \Cthulhu\Errors\TypeError {
  function __construct(string $variable_name) {
    parent::__construct("use of undeclared variable '$variable_name'");
  }
}
