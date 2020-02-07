<?php

namespace Curry_5 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    return $d * $e + $f;
  }
  function foo($a, $b) {
    print("enter foo" . "\n");
    return fn ($c) => \Curry_5\inner($a, $b, $c);
  }
  function main() {
    $x = \Curry_5\foo(1, 2);
    $y = $x(3);
    print((string)$y . "\n");
  }
}

namespace {
  \Curry_5\main(null);
}
