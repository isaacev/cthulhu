<?php

namespace Cthulhu\Parser\Errors;

use Cthulhu\Parser\Lexer\Token;

class UnexpectedToken extends \Cthulhu\Errors\SyntaxError {
  function __construct(Token $found, ?string $wanted_type = null) {
    $where = $found->span->from;
    if ($wanted_type !== null) {
      parent::__construct("unexpected $found->type at $where, wanted $wanted_type");
    } else {
      parent::__construct("unexpected $found->type at $where");
    }
  }
}
