<?php

namespace Cthulhu\lib\test;

class TestFailed extends TestResult {
  public TestOutput $found;

  public function __construct(Test $test, TestOutput $found, float $buildtime, float $runtime) {
    parent::__construct($test, $buildtime, $runtime);
    $this->found = $found;
  }
}
