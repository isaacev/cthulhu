<?php

namespace Cthulhu\lib\cli\internals;

class StrFlagGrammar extends FlagGrammar {
  public string $arg_name;
  public ?array $pattern;

  public function __construct(string $id, ?string $short, string $description, string $arg_name, ?array $pattern) {
    parent::__construct($id, $short, $description);
    $this->arg_name = $arg_name;
    $this->pattern  = $pattern;
  }

  public function completions(): array {
    if ($this->has_short_form()) {
      return [
        "-$this->short",
        "--$this->id",
      ];
    } else {
      return [ "--$this->id" ];
    }
  }

  public function matches(string $token): bool {
    return (
      $token === $this->id ||
      $token === $this->short
    );
  }

  public function parse(string $token, Scanner $scanner): FlagResult {
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

  public function full_name(): string {
    return parent::full_name() . ' <' . $this->arg_name . '>';
  }
}
