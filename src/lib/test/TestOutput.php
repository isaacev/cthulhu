<?php

namespace Cthulhu\lib\test;

class TestOutput {
  public string $php;
  public string $out;

  public function __construct(string $php, string $out) {
    $this->php = $php;
    $this->out = $out;
  }

  public function equals(self $other): bool {
    return (
      $this->php === $other->php &&
      $this->out === $other->out
    );
  }
}
