<?php

namespace Cthulhu\Parser\Errors;

use Cthulhu\Parser\Lexer\Token;

class UnexpectedToken extends \Exception {
  function __construct(Token $found, ?string $wanted_type = null) {
    if ($wanted_type !== null) {
      parent::__construct("unexpected $found->type at $found->span, wanted $wanted_type");
    } else {
      parent::__construct("unexpected $found->type at $found->span");
    }
  }
}
