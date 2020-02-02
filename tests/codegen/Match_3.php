<?php

namespace Match_3 {
  function main() {
    $b = 5;
    if ($b == 0) {
      $c = 0;
    } else if (true) {
      $n = $b;
      $c = $n + 1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $next = $c;
    print((string)$next . "\n");
  }
}

namespace {
  \Match_3\main(null);
}
