<?php

namespace Cthulhu\lib\test;

class TestOutput {
  public string $php;
  public string $out;

  function __construct(string $php, string $out) {
    $this->php = $php;
    $this->out = $out;
  }

  function equals(self $other): bool {
    return (
      $this->php === $other->php &&
      $this->out === $other->out
    );
  }
}
