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
    if ($d instanceof \Prelude\Some) {
      $d->{0};
    } else if ($d instanceof \Prelude\None) {
      -1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

namespace {
  \List_1\main(null);
}
