<?php

namespace Cthulhu\lib\test;

abstract class TestResult {
  public $test;

  function __construct(Test $test) {
    $this->test = $test;
  }
}
