<?php

namespace Cthulhu\utils\cli\internals;

class BoolFlagGrammar extends FlagGrammar {
  function matches(string $token): bool {
    return (
      $token === $this->id ||
      $token === "no-$this->id"
    );
  }

  function parse(string $token, Scanner $scanner): FlagResult {
    if ($token === "no-$this->id") {
      return new FlagResult($this->id, false);
    } else {
      return new FlagResult($this->id, true);
    }
  }

  function full_name(): string {
    return "--$this->id";
  }
}
