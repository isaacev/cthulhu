<?php

namespace Cthulhu\lib\test;

abstract class TestResult {
  public $test;
  public $runtime_in_ms;

  function __construct(Test $test, float $runtime_in_ms) {
    $this->test          = $test;
    $this->runtime_in_ms = $runtime_in_ms;
  }
}
