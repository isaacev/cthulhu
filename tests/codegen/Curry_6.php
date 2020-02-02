<?php

namespace Curry_6 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    return $d * $e + $f;
  }
  function foo($a, $b) {
    print("enter foo" . "\n");
    return fn ($d) => \Curry_6\inner($a, $b, $d);
  }
  function main() {
    $x = \Curry_6\foo(1, 2);
  }
}

namespace {
  \Curry_6\main(null);
}
