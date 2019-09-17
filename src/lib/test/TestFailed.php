<?php

namespace Cthulhu\lib\test;

class TestFailed extends TestResult {
  public $found;

  function __construct(Test $test, TestOutput $found) {
    parent::__construct($test);
    $this->found = $found;
  }
}
