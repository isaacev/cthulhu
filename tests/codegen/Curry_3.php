<?php

namespace Curry_3 {
  function foo($a, $b, $c) {
    $d = ($a + $b) * $c;
    return $d;
  }
  function main() {
    $x = fn ($b) => \Curry_3\foo(1, 2, $b);
    $y = $x(3);
    print((string)$y . "\n");
  }
}

namespace {
  \Curry_3\main(null);
}
