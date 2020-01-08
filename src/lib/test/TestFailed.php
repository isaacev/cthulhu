<?php

namespace Cthulhu\lib\test;

class TestFailed extends TestResult {
  public TestOutput $found;

  public function __construct(Test $test, TestOutput $found, float $runtime_in_ms) {
    parent::__construct($test, $runtime_in_ms);
    $this->found = $found;
  }
}
