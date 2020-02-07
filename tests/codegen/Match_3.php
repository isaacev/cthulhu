<?php

namespace Match_3 {
  function main() {
    $a = 5;
    if ($a == 0) {
      $next = 0;
    } else if (true) {
      $next = $a + 1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print((string)$next . "\n");
  }
}

namespace {
  \Match_3\main(null);
}
