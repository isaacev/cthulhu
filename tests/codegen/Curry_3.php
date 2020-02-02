<?php

namespace curry_3 {
  function foo($a, $b, $c) {
    return ($a + $b) * $c;
  }

  // #[entry]
  function main() {
    $x = fn ($a) => \curry_3\foo(1, 2, $a);
    $y = $x(3);
    print((string)$y . "\n");
  }
}

namespace {
  \curry_3\main();
}
