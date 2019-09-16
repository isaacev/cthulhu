<?php

namespace Cthulhu\utils\test;

abstract class TestResult {
  public $test;

  function __construct(Test $test) {
    $this->test = $test;
  }
}
