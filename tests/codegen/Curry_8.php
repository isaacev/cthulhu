<?php

namespace Curry_8 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    return $d * $e + $f;
  }
  function foo($a) {
    print("enter foo" . "\n");
    return fn ($b, $c) => \Curry_8\inner($a, $b, $c);
  }
  function main() {
    $a = \Curry_8\foo(1);
    fn ($b) => $a(2, $b);
    $y = \Curry_8\foo(3)(4, 5);
    print((string)$y . "\n");
    return null;
  }
}

namespace {
  \Curry_8\main(null);
}
