<?php

namespace Curry_2 {
  function foo($a, $b, $c) {
    return ($a + $b) * $c;
  }
  function main() {
    $x = \Curry_2\foo(1, 2, 3);
    print((string)$x . "\n");
    return null;
  }
}

namespace {
  \Curry_2\main(null);
}
