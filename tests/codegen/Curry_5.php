<?php

namespace curry_5 {
  function inner($d, $e, $f) {
    print("enter inner\n");
    return $d * $e + $f;
  }

  function foo($a, $b) {
    print("enter foo\n");
    return fn ($c) => \curry_5\inner($a, $b, $c);
  }

  // #[entry]
  function main() {
    $x = \curry_5\foo(1, 2);
    $y = $x(3);
    print((string)$y . "\n");
  }
}

namespace {
  \curry_5\main();
}
