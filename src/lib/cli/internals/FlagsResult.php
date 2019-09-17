<?php

namespace Cthulhu\lib\cli\internals;

class FlagsResult {
  function __construct(FlagsGrammar $grammar, array $flags) {
    $this->grammar = $grammar;
    $this->flags = $flags;
  }
}
