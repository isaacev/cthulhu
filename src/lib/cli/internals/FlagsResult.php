<?php

namespace Cthulhu\lib\cli\internals;

class FlagsResult {
  public FlagsGrammar $grammar;
  public array $flags;

  function __construct(FlagsGrammar $grammar, array $flags) {
    $this->grammar = $grammar;
    $this->flags   = $flags;
  }
}
