<?php

namespace curry_1 {
  function foo($a, $b, $c) {
    return ($a + $b) * $c;
  }

  // #[entry]
  function main() {
    $x = '\curry_1\foo';
    print((string)$x(1, 2, 3) . "\n");
  }
}

namespace {
  \curry_1\main();
}
