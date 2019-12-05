<?php

namespace union_6 {
  abstract class Result {}

  class Left extends \union_6\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class Right extends \union_6\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  // #[entry]
  function main() {
    $b = \mt_rand(0, 5);
    if ($b == 0) {
      $a = new \union_6\Left(true);
    } else if ($b == 1) {
      $a = new \union_6\Left(true);
    } else if ($b == 2) {
      $a = new \union_6\Left(false);
    } else if (true) {
      $a = new \union_6\Right("unknown integer");
    }
    $r = $a;
  }
}

namespace {
  \union_6\main();
}
