<?php

namespace Cthulhu\lib\cli\internals;

class IntFlagGrammar extends FlagGrammar {
  public string $arg_name;

  function __construct(string $id, ?string $short, string $description, string $arg_name) {
    parent::__construct($id, $short, $description);
    $this->arg_name = $arg_name;
  }

  function completions(): array {
    if ($this->has_short_form()) {
      return [
        "-$this->short",
        "--$this->id",
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

    if (preg_match('/^\d+$/', $value)) {
      $value = intval($value, 10);
    } else {
      $fmt = 'flag `%s` expected an integer argument instead of `%s`';
      Scanner::fatal_error($fmt, $this->id, $value);
    }

    return new FlagResult($this->id, intval($value));
  }

  function full_name(): string {
    return parent::full_name() . ' <' . $this->arg_name . '>';
  }
}
