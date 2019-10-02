<?php

namespace Cthulhu\lib\cli\internals;

class StrFlagGrammar extends FlagGrammar {
  public $pattern;

  function __construct(string $id, ?string $short, string $description, ?array $pattern) {
    parent::__construct($id, $short, $description);
    $this->pattern = $pattern;
  }

  function completions(): array {
    if ($this->has_short_form()) {
      return [
        "-$this->short",
        "--$this->id"
      ];
    } else {
      return [ "--$this->id" ];
    }
  }

  function matches(string $token): bool {
    return (
      $token === $this->id ||
      $token === $this->short
    );
  }

  function parse(string $token, Scanner $scanner): FlagResult {
    if ($scanner->is_empty()) {
      $fmt = 'flag `$this->id` requires a result';
      Scanner::fatal_error($fmt, $this->id);
    }

    $value = $scanner->advance();

    if (is_array($this->pattern)) {
      if (in_array($value, $this->pattern)) {
        return new FlagResult($this->id, $value);
      } else {
        $fmt = 'flag `%s` expected one of %s';
        Scanner::fatal_error($fmt, $this->id, implode(', ', $this->pattern));
      }
    }

    return new FlagResult($this->id, $value);
  }
}
