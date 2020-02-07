<?php

namespace Closure_3 {
  function main() {
    $a = ">> ";
    $c = function($b, $c) use ($a) {
      return $a . $b . $c;
    };
    $d = $c("hello", "world");
    print($d . "\n");
  }
}

namespace {
  \Closure_3\main(null);
}
