<?php

namespace Closure_1 {
  function main() {
    $c = function($a, $b) {
      return $a . $b;
    };
    $d = $c("hello", "world");
    print($d . "\n");
  }
}

namespace {
  \Closure_1\main(null);
}
