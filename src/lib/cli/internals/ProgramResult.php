<?php

namespace Cthulhu\lib\cli\internals;

class ProgramResult {
  public ProgramGrammar $grammar;
  public FlagsResult $flags;
  public ?SubcommandResult $subcommand;

  public function __construct(ProgramGrammar $grammar, FlagsResult $flags, ?SubcommandResult $subcommand) {
    $this->grammar    = $grammar;
    $this->flags      = $flags;
    $this->subcommand = $subcommand;
  }
}
