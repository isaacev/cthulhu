<?php

namespace Cthulhu\utils\cli\internals;

class ProgramResult {
  function __construct(ProgramGrammar $grammar, array $flags, ?SubcommandResult $subcommand) {
    $this->grammar = $grammar;
    $this->flags = $flags;
    $this->subcommand = $subcommand;
  }
}
