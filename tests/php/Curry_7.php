<?php

namespace Curry_7 {
  function inner($d, $e, $f) {
    print("enter inner\n");
    return $d * $e + $f;
  }

  function foo($a, $b) {
    print("enter foo\n");
    return fn ($c) => \Curry_7\inner($a, $b, $c);
  }

  function main() {
    \Curry_7\foo(1, 2);
    $y = \Curry_7\foo(3, 4)(5);
    print((string)$y . "\n");
    return null;
  }
}

namespace {
  \Curry_7\main(null);
}
