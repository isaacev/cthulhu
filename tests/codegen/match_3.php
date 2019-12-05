<?php

namespace match_3 {
  // #[entry]
  function main() {
    $b = 5;
    if ($b == 0) {
      $a = 0;
    } else if (true) {
      $n = $b;
      $a = $n + 1;
    }
    $next = $a;
    print((string)$next . "\n");
  }
}

namespace {
  \match_3\main();
}
