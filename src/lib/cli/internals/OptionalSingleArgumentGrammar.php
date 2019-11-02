<?php

namespace Cthulhu\lib\cli\internals;

class OptionalSingleArgumentGrammar extends ArgumentGrammar {
  function parse(Scanner $scanner): ArgumentResult {
    if ($scanner->is_empty()) {
      return new MissingArgumentResult($this->id);
    }
    return new ArgumentResult($this->id, $scanner->advance());
  }

  function full_name(): string {
    return "[$this->id]";
  }
}
