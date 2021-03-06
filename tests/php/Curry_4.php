<?php

namespace Curry_4 {
  function inner($d, $e, $f) {
    print("enter inner\n");
    return $d * $e + $f;
  }

  function foo($a, $b) {
    print("enter foo\n");
    return fn ($c) => \Curry_4\inner($a, $b, $c);
  }

  function main() {
    $x = \Curry_4\foo(1, 2)(3);
    print((string)$x . "\n");
    return null;
  }
}

namespace {
  \Curry_4\main(null);
}
