<?php

namespace Cthulhu\lib\cli\internals;

class FlagsResult {
  public $grammar;
  public $flags;

  function __construct(FlagsGrammar $grammar, array $flags) {
    $this->grammar = $grammar;
    $this->flags   = $flags;
  }
}
