<?php

namespace Curry_8 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    return $d * $e + $f;
  }
  function foo($a) {
    print("enter foo" . "\n");
    return fn ($c, $d) => \Curry_8\inner($a, $c, $d);
  }
  function main() {
    $b = \Curry_8\foo(1);
    fn ($c) => $b(2, $c);
    $y = \Curry_8\foo(3)(4, 5);
    print((string)$y . "\n");
  }
}

namespace {
  \Curry_8\main(null);
}
