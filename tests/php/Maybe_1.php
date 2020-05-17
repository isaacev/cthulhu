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

namespace Maybe_1 {
  function main() {
    $a = new \Prelude\Some("abc");
    if ($a instanceof \Prelude\Some) {
      $_a = $a->{0};
      return print($_a . "\n");
    } else if ($a instanceof \Prelude\None) {
      return print("none\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

namespace {
  \Maybe_1\main(null);
}
