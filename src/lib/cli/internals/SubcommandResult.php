<?php

namespace Cthulhu\lib\cli\internals;

class SubcommandResult {
  public SubcommandGrammar $grammar;
  public FlagsResult $flags;
  public array $arguments;

  /**
   * @param SubcommandGrammar $grammar
   * @param FlagsResult       $flags
   * @param ArgumentResult[]  $arguments
   */
  public function __construct(SubcommandGrammar $grammar, FlagsResult $flags, array $arguments) {
    $this->grammar   = $grammar;
    $this->flags     = $flags;
    $this->arguments = $arguments;
  }
}
