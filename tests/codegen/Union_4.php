<?php

namespace Prelude {
  abstract class Maybe {}
  class Some extends \Prelude\Maybe {
    function __construct($a) {
      $this->{0} = $a;
    }
  }
  class None extends \Prelude\Maybe {
    function __construct() {
      // empty
    }
  }
}

namespace Union_4 {
  function main() {
    if (0 < 5) {
      $x = new \Prelude\Some("hello");
    } else {
      $x = new \Prelude\None();
    }
    if ($x instanceof \Prelude\Some) {
      $s = $x->{0};
      $a = $s;
    } else if ($x instanceof \Prelude\None) {
      $a = "none";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($a . "\n");
  }
}

namespace {
  \Union_4\main(null);
}
