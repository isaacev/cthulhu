<?php

namespace Cthulhu\lib\cli\internals;

class SubcommandResult {
  public SubcommandGrammar $grammar;
  public FlagsResult $flags;
  public array $arguments;

  function __construct(SubcommandGrammar $grammar, FlagsResult $flags, array $arguments) {
    $this->grammar   = $grammar;
    $this->flags     = $flags;
    $this->arguments = $arguments;
  }
}
