<?php

namespace Cthulhu\lib\cli\internals;

class VariadicArgumentGrammar extends ArgumentGrammar {
  function parse(Scanner $scanner): ArgumentResult {
    $values = [];
    while ($scanner->not_empty()) {
      $values[] = $scanner->advance();
    }
    return new ArgumentResult($this->id, $values);
  }

  function full_name(): string {
    return "[...$this->id]";
  }
}
