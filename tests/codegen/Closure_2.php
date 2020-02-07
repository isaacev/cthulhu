<?php

namespace Closure_2 {
  function main() {
    $a = ">> ";
    $c = function($_a, $b) {
      return $_a . $b;
    };
    $d = $a . $c("hello", "world");
    print($d . "\n");
    return null;
  }
}

namespace {
  \Closure_2\main(null);
}
