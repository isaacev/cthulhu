<?php

namespace Kernel\Types {
  abstract class Maybe {}

  class Just extends \Kernel\Types\Maybe {
    function __construct($a) {
      $this->{0} = $a;
    }
  }

  class None extends \Kernel\Types\Maybe {}
}

namespace union_4 {
  // #[entry]
  function main() {
    if (0 < 5) {
      $a = new \Kernel\Types\Just("hello");
    } else {
      $a = new \Kernel\Types\None();
    }
    $x = $a;
    if ($x instanceof \Kernel\Types\Just) {
      $s = $x->{0};
      $b = $s;
    } else if ($x instanceof \Kernel\Types\None) {
      $b = "none";
    }
    print($b . "\n");
  }
}

namespace {
  \union_4\main();
}
