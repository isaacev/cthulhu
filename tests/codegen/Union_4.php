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
      $b = new \Prelude\Some("hello");
    } else {
      $b = new \Prelude\None();
    }
    $x = $b;
    $c = $x;
    if ($c instanceof \Prelude\Some) {
      $s = $c->{0};
      $d = $s;
    } else if ($c instanceof \Prelude\None) {
      $d = "none";
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print($d . "\n");
  }
}

namespace {
  \Union_4\main(null);
}
