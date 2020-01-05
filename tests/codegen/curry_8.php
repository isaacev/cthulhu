<?php

namespace curry_8 {
  function inner($d, $e, $f) {
    print("enter inner\n");
    return $d * $e + $f;
  }

  function foo($a) {
    print("enter foo\n");
    return fn ($b, $c) => \curry_8\inner($a, $b, $c);
  }

  // #[entry]
  function main() {
    $a = \curry_8\foo(1);
    $x = fn ($b) => $a(2, $b);
    $y = \curry_8\foo(3)(4, 5);
    print((string)$y . "\n");
  }
}

namespace {
  \curry_8\main();
}
