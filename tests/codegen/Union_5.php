<?php

namespace union_5 {
  abstract class Result {}

  class Left extends \union_5\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class Right extends \union_5\Result {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  function attempt($success, $val) {
    if ($success) {
      $a = new \union_5\Left($val);
    } else {
      $a = new \union_5\Right("unable to generate a true value");
    }
    return $a;
  }

  // #[entry]
  function main() {
    $b = \union_5\attempt(true, true);
    if ($b instanceof \union_5\Left && $b->{0} == true) {
      print("was true\n");
    } else if ($b instanceof \union_5\Left && $b->{0} == false) {
      print("was false\n");
    } else if ($b instanceof \union_5\Right) {
      $msg = $b->{0};
      print($msg . "\n");
    }
    $a;
    $d = \union_5\attempt(true, false);
    if ($d instanceof \union_5\Left && $d->{0} == true) {
      print("was true\n");
    } else if ($d instanceof \union_5\Left && $d->{0} == false) {
      print("was false\n");
    } else if ($d instanceof \union_5\Right) {
      $_msg = $d->{0};
      print($_msg . "\n");
    }
    $c;
  }
}

namespace {
  \union_5\main();
}
