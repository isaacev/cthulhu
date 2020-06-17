<?php

namespace Cthulhu\lib\test;

abstract class TestResult {
  public Test $test;
  public float $buildtime;
  public float $runtime;

  public function __construct(Test $test, float $buildtime, float $runtime) {
    $this->test      = $test;
    $this->buildtime = $buildtime;
    $this->runtime   = $runtime;
  }
}
