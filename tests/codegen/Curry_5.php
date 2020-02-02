<?php

namespace Curry_5 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    $a = $d * $e + $f;
    return $a;
  }
  function foo($a, $b) {
    print("enter foo" . "\n");
    $c = fn ($d) => \Curry_5\inner($a, $b, $d);
    return $c;
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
