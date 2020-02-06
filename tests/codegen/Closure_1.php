<?php

namespace Closure_1 {
  function main() {
    $c = function($_a, $b) {
      return $_a . $b;
    };
    $d = $c("hello", "world");
    print($d . "\n");
  }
}

namespace {
  \Closure_1\main(null);
}
