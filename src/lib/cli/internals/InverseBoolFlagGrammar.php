<?php

namespace Cthulhu\lib\cli\internals;

class InverseBoolFlagGrammar extends BoolFlagGrammar {
  public function parse(string $token, Scanner $scanner): FlagResult {
    if ($token === "no-$this->id") {
      return new FlagResult($this->id, false);
    } else {
      return new FlagResult($this->id, true);
    }
  }

  public function full_name(): string {
    if ($this->has_short_form()) {
      return "-$this->short, --no-$this->id";
    } else {
      return "    --no-$this->id";
    }
  }
}
