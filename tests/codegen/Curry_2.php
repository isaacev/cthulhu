<?php

namespace curry_2 {
  function foo($a, $b, $c) {
    return ($a + $b) * $c;
  }

  // #[entry]
  function main() {
    $x = \curry_2\foo(1, 2, 3);
    print((string)$x . "\n");
  }
}

namespace {
  \curry_2\main();
}
