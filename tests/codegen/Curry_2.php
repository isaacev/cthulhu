<?php

namespace Curry_2 {
  function foo($a, $b, $c) {
    $d = ($a + $b) * $c;
    return $d;
  }
  function main() {
    $x = (fn ($b) => \Curry_2\foo(1, 2, $b))(3);
    print((string)$x . "\n");
  }
}

namespace {
  \Curry_2\main(null);
}
