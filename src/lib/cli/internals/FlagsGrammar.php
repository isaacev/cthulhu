<?php

namespace Cthulhu\lib\cli\internals;

class FlagsGrammar {
  public array $flags = [];

  public function add(FlagGrammar $flag) {
    $this->flags[] = $flag;
  }

  public function get(string $token): ?FlagGrammar {
    foreach ($this->flags as $flag) {
      if ($flag->matches($token)) {
        return $flag;
      }
    }
    return null;
  }

  public function completions(): array {
    $comps = [];
    foreach ($this->flags as $flag) {
      $comps = array_merge($comps, $flag->completions());
    }
    return $comps;
  }

  public function parse_single_flag(Scanner $scanner): FlagResult {
    $next = $scanner->advance();
    if (preg_match('/^(--(\S+))|(-([a-zA-Z0-9]))$/', $next, $matches)) {
      $token = empty($matches[2]) ? $matches[4] : $matches[2];
      if ($grammar = $this->get($token)) {
        return $grammar->parse($token, $scanner);
      }
    }
    Scanner::fatal_error('unknown flag: `%s`', $next);
    die(1);
  }

  public function parse(Scanner $scanner): FlagsResult {
    $flag_results = [];
    while ($scanner->not_empty()) {
      if ($scanner->next_is('/^--$/')) {
        /**
         * A double dash without a flag name is a signal to start parsing
         * command arguments so when it's found, immediately break out of the
         * flag-parsing loop and return the flags that were found.
         */
        $scanner->advance();
        break;
      } else if ($scanner->next_starts_with('-')) {
        $flag_results[] = $this->parse_single_flag($scanner);
      } else if ($scanner->next_starts_with('-')) {
        Scanner::fatal_error("unknown flag: `%s`", $scanner->advance());
      } else {
        break;
      }
    }
    return new FlagsResult($this, $flag_results);
  }
}
