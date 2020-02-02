<?php

namespace Curry_7 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    $a = $d * $e + $f;
    return $a;
  }
  function foo($a, $b) {
    print("enter foo" . "\n");
    $c = fn ($d) => \Curry_7\inner($a, $b, $d);
    return $c;
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
