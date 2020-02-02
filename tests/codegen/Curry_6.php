<?php

namespace curry_6 {
  function inner($d, $e, $f) {
    print("enter inner\n");
    return $d * $e + $f;
  }

  function foo($a, $b) {
    print("enter foo\n");
    return fn ($c) => \curry_6\inner($a, $b, $c);
  }

  // #[entry]
  function main() {
    $x = \curry_6\foo(1, 2);
  }
}

namespace {
  \curry_6\main();
}
