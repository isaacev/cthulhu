<?php

namespace Closure_3 {
  function main() {
    $_a = ">> ";
    $c = function($b, $c) use ($_a) {
      return $_a . $b . $c;
    };
    $d = $c("hello", "world");
    print($d . "\n");
  }
}

namespace {
  \Closure_3\main(null);
}
