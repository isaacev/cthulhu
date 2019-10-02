<?php

namespace Cthulhu\lib\cli\internals;

abstract class FlagGrammar implements Describeable {
  function __construct(string $id, ?string $short, string $description) {
    $this->id = $id;
    $this->short = $short;
    $this->description = $description;
  }

  function has_short_form(): bool {
    return $this->short !== null;
  }

  abstract function completions(): array;
  abstract function matches(string $token): bool;
  abstract function parse(string $token, Scanner $scanner): FlagResult;

  function full_name(): string {
    if ($this->has_short_form()) {
      return "-$this->short, --$this->id";
    } else {
      return "    --$this->id";
    }
  }

  function description(): string {
    return $this->description;
  }
}
