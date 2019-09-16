<?php

namespace Cthulhu\utils\test;

class TestOutput {
  public $stdout;
  public $stderr;

  function __construct(string $stdout, string $stderr) {
    $this->stdout = $stdout;
    $this->stderr = $stderr;
  }

  function equals(TestOutput $other): bool {
    return (
      $this->stdout === $other->stdout &&
      $this->stderr === $other->stderr
    );
  }
}
