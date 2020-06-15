<?php

namespace Cthulhu\lib\test;

class TestOutput {
  public string $php;
  public string $stdout;
  public string $stderr;

  public function __construct(string $php, string $stdout, string $stderr) {
    $this->php    = $php;
    $this->stdout = $stdout;
    $this->stderr = $stderr;
  }

  public function equals(self $other): bool {
    return (
      $this->php === $other->php &&
      $this->stdout === $other->stdout &&
      $this->stderr === $other->stderr
    );
  }
}
