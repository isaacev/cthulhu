<?php

namespace Branch_1 {
  function main() {
    if (true) {
      $a = function($_a, $b) {
        return $_a + $b;
      };
    } else {
      $a = function($c, $d) {
        return $c + $d;
      };
    }
    print((string)$a(2, 3) . "\n");
    return null;
  }
}

namespace {
  \Branch_1\main(null);
}
