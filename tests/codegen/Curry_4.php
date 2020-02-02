<?php

namespace Curry_4 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    $a = $d * $e + $f;
    return $a;
  }
  function foo($a, $b) {
    print("enter foo" . "\n");
    $c = fn ($d) => \Curry_4\inner($a, $b, $d);
    return $c;
  }
  function main() {
    $x = \Curry_4\foo(1, 2)(3);
    print((string)$x . "\n");
  }
}

namespace {
  \Curry_4\main(null);
}
