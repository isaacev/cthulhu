<?php

namespace Prelude\Maybe {
  function with_default($d, $m) {
    if ($m instanceof \Prelude\Some) {
      $_a = $m->{0};
      return $_a;
    } else if ($m instanceof \Prelude\None) {
      return $d;
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

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

namespace Maybe_4 {
  function main() {
    print(\Prelude\Maybe\with_default("who knows?", new \Prelude\None()) . "\n");
    return null;
  }
}

namespace {
  \Maybe_4\main(null);
}
