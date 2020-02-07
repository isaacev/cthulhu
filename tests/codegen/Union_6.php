<?php

namespace Union_6 {
  abstract class Result {}
  class Left extends \Union_6\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class Right extends \Union_6\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  function main() {
    $a = \mt_rand(0, 5);
    if ($a == 0) {
      new \Union_6\Left(true);
    } else if ($a == 1) {
      new \Union_6\Left(true);
    } else if ($a == 2) {
      new \Union_6\Left(false);
    } else if (true) {
      new \Union_6\Right("unknown integer");
    } else {
      die("match expression did not cover all possibilities\n");
    }
    return null;
  }
}

namespace {
  \Union_6\main(null);
}
