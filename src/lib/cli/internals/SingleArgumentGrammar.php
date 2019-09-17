<?php

namespace Cthulhu\lib\cli\internals;

class SingleArgumentGrammar extends ArgumentGrammar {
  function parse(Scanner $scanner): ArgumentResult {
    if ($scanner->is_empty()) {
      Scanner::fatal_error('missing required `%s` argument', $this->id);
    }
    return new ArgumentResult($this->id, $scanner->advance());
  }

  function full_name(): string {
    return "<$this->id>";
  }
}
