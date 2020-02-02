<?php

namespace Curry_7 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    return $d * $e + $f;
  }
  function foo($a, $b) {
    print("enter foo" . "\n");
    return fn ($d) => \Curry_7\inner($a, $b, $d);
  }
  function main() {
    $x = \Curry_7\foo(1, 2);
    $y = \Curry_7\foo(3, 4)(5);
    print((string)$y . "\n");
  }
}

namespace {
  \Curry_7\main(null);
}
