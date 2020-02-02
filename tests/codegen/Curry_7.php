<?php

namespace curry_7 {
  function inner($d, $e, $f) {
    print("enter inner\n");
    return $d * $e + $f;
  }

  function foo($a, $b) {
    print("enter foo\n");
    return fn ($c) => \curry_7\inner($a, $b, $c);
  }

  // #[entry]
  function main() {
    $x = \curry_7\foo(1, 2);
    $y = \curry_7\foo(3, 4)(5);
    print((string)$y . "\n");
  }
}

namespace {
  \curry_7\main();
}
