<?php

namespace Curry_1 {
  function foo($a, $b, $c) {
    $d = ($a + $b) * $c;
    return $d;
  }
  function main() {
    $x = '\Curry_1\foo';
    print((string)$x(1, 2, 3) . "\n");
  }
}

namespace {
  \Curry_1\main(null);
}
