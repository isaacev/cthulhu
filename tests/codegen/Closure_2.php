<?php

namespace Closure_2 {
  function main() {
    $_a = ">> ";
    $c = function($a_1, $b) {
      return $a_1 . $b;
    };
    $d = $_a . $c("hello", "world");
    print($d . "\n");
  }
}

namespace {
  \Closure_2\main(null);
}
