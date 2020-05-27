<?php

namespace Curry_3 {
  function foo($a, $b, $c) {
    return ($a + $b) * $c;
  }

  function main() {
    $x = fn ($a) => \Curry_3\foo(1, 2, $a);
    $y = $x(3);
    print((string)$y . "\n");
    return null;
  }
}

namespace {
  \Curry_3\main(null);
}
