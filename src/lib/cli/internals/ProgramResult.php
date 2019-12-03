<?php

namespace Cthulhu\lib\cli\internals;

class ProgramResult {
  public $grammar;
  public $flags;
  public $subcommand;

  function __construct(ProgramGrammar $grammar, FlagsResult $flags, ?SubcommandResult $subcommand) {
    $this->grammar    = $grammar;
    $this->flags      = $flags;
    $this->subcommand = $subcommand;
  }
}
