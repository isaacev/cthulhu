<?php

namespace curry_4 {
  function inner($d, $e, $f) {
    print("enter inner\n");
    return $d * $e + $f;
  }

  function foo($a, $b) {
    print("enter foo\n");
    return fn ($c) => \curry_4\inner($a, $b, $c);
  }

  // #[entry]
  function main() {
    $x = \curry_4\foo(1, 2)(3);
    print((string)$x . "\n");
  }
}

namespace {
  \curry_4\main();
}
