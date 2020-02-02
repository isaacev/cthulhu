<?php

namespace Curry_6 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    $a = $d * $e + $f;
    return $a;
  }
  function foo($a, $b) {
    print("enter foo" . "\n");
    $c = fn ($d) => \Curry_6\inner($a, $b, $d);
    return $c;
  }
  function main() {
    $x = \Curry_6\foo(1, 2);
  }
}

namespace {
  \Curry_6\main(null);
}
