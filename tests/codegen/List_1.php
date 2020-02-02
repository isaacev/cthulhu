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

namespace List_1 {
  function main() {
    $l = [
      1,
      2,
      3
    ];
    if (\array_key_exists(0, $l)) {
      $d = new \Prelude\Some($l[0]);
    } else {
      $d = new \Prelude\None();
    }
    $b = $d;
    if ($b instanceof \Prelude\Some) {
      $p = $b->{0};
      $c = $p;
    } else if ($b instanceof \Prelude\None) {
      $c = -1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $n = 4 + $c;
  }
}

namespace {
  \List_1\main(null);
}
