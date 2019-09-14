<?php

namespace Cthulhu\utils\cli\internals;

abstract class HasFlagsGrammar {
  protected $flag_grammars;

  function add_flag(FlagGrammar $grammar) {
    $this->flag_grammars[] = $grammar;
  }

  function flag_completions(): array {
    $comps = [];
    foreach ($this->flag_grammars as $flag_grammar) {
      $comps = array_merge($comps, $flag_grammar->completions());
    }
    return $comps;
  }

  function get_flag(string $token): ?FlagGrammar {
    foreach ($this->flag_grammars as $grammar) {
      if ($grammar->matches($token)) {
        return $grammar;
      }
    }
    return null;
  }

  function parse_single_flag(Scanner $scanner): FlagResult {
    $next = $scanner->advance();
    preg_match('/^--(\S+)/', $next, $matches);
    if (array_key_exists(1, $matches)) {
      $token = $matches[1];
      if ($grammar = $this->get_flag($token)) {
        return $grammar->parse($token, $scanner);
      }
    }
    Scanner::fatal_error('unknown flag: `%s`', $next);
  }

  function parse_flags(Scanner $scanner): array {
    $flags = [];
    while ($scanner->not_empty()) {
      if ($scanner->next_is('/^--$/')) {
        /**
         * A double dash without a flag name is a signal to start parsing
         * command arguments so when it's found, immediately break out of the
         * flag-parsing loop and return the flags that were found.
         */
        $scanner->advance();
        break;
      } else if ($scanner->next_starts_with('--')) {
        $flags[] = $this->parse_single_flag($scanner);
      } else if ($scanner->next_starts_with('-')) {
        Scanner::fatal_error("unknown flag: `%s`", $scanner->advance());
      } else {
        break;
      }
    }
    return $flags;
  }
}
