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

namespace pipe_1 {
  function or_else($fallback, $m) {
    if ($m instanceof \Kernel\Types\Just) {
      $_a = $m->{0};
      $a = $_a;
    } else if ($m instanceof \Kernel\Types\None) {
      $a = $fallback;
    }
    return $a;
  }

  // #[entry]
  function main() {
    print(\pipe_1\or_else("no message", new \Kernel\Types\Just("hello world")) . "\n");
  }
}

namespace {
  \pipe_1\main();
}
