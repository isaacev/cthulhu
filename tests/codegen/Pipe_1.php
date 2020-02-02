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

namespace Pipe_1 {
  function or_else($fallback, $m) {
    if ($m instanceof \Prelude\Some) {
      $_a = $m->{0};
      return $_a;
    } else if ($m instanceof \Prelude\None) {
      return $fallback;
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
  function main() {
    print((fn ($b) => \Pipe_1\or_else("no message", $b))(new \Prelude\Some("hello world")) . "\n");
  }
}

namespace {
  \Pipe_1\main(null);
}
