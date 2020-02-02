<?php

namespace Curry_8 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    $a = $d * $e + $f;
    return $a;
  }
  function foo($a) {
    print("enter foo" . "\n");
    $b = fn ($c, $d) => \Curry_8\inner($a, $c, $d);
    return $b;
  }
  function main() {
    $b = \Curry_8\foo(1);
    $x = fn ($c) => $b(2, $c);
    $y = \Curry_8\foo(3)(4, 5);
    print((string)$y . "\n");
  }
}

namespace {
  \Curry_8\main(null);
}
