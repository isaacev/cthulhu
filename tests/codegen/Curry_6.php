<?php

namespace Curry_6 {
  function inner($d, $e, $f) {
    print("enter inner" . "\n");
    return $d * $e + $f;
  }
  function foo($a, $b) {
    print("enter foo" . "\n");
    return fn ($c) => \Curry_6\inner($a, $b, $c);
  }
  function main() {
    \Curry_6\foo(1, 2);
    return null;
  }
}

namespace {
  \Curry_6\main(null);
}
