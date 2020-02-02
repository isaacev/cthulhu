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
    $b = $m;
    if ($b instanceof \Prelude\Some) {
      $_a = $b->{0};
      $c = $_a;
    } else if ($b instanceof \Prelude\None) {
      $c = $fallback;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $a = $c;
    return $a;
  }
  function main() {
    print((fn ($b) => \Pipe_1\or_else("no message", $b))(new \Prelude\Some("hello world")) . "\n");
  }
}

namespace {
  \Pipe_1\main(null);
}
