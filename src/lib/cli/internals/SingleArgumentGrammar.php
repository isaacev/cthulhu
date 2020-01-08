<?php

namespace Cthulhu\lib\cli\internals;

class SingleArgumentGrammar extends ArgumentGrammar {
  public function parse(Scanner $scanner): ArgumentResult {
    if ($scanner->is_empty()) {
      Scanner::fatal_error('missing required `%s` argument', $this->id);
    }
    return new ArgumentResult($this->id, $scanner->advance());
  }

  public function full_name(): string {
    return "<$this->id>";
  }
}
