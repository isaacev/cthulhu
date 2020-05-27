<?php

namespace Curry_1 {
  function foo($a, $b, $c) {
    return ($a + $b) * $c;
  }

  function main() {
    $x = '\Curry_1\foo';
    print((string)$x(1, 2, 3) . "\n");
    return null;
  }
}

namespace {
  \Curry_1\main(null);
}
