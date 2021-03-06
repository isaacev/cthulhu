<?php

namespace Cthulhu\lib\cli\internals;

class BoolFlagGrammar extends FlagGrammar {
  /**
   * @return string[]
   */
  public function completions(): array {
    if ($this->has_short_form()) {
      return [
        "-$this->short",
        "--$this->id",
        "--no-$this->id",
      ];
    } else {
      return [
        "--$this->id",
        "--no-$this->id",
      ];
    }
  }

  public function matches(string $token): bool {
    return (
      $token === $this->id ||
      $token === "no-$this->id" ||
      $token === $this->short
    );
  }

  public function parse(string $token, Scanner $scanner): FlagResult {
    if ($token === "no-$this->id") {
      return new FlagResult($this->id, false);
    } else {
      return new FlagResult($this->id, true);
    }
  }
}
