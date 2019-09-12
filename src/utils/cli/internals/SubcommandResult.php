<?php

namespace Cthulhu\utils\cli\internals;

class SubcommandResult {
  function __construct(SubcommandGrammar $grammar, array $flags, array $arguments) {
    $this->grammar = $grammar;
    $this->flags = $flags;
    $this->arguments = $arguments;
  }
}
