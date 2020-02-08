<?php

namespace Match_3 {
  function main() {
    $a = 5;
    if ($a == 0) {
      $next = 0;
    } else {
      $next = $a + 1;
    }
    print((string)$next . "\n");
    return null;
  }
}

namespace {
  \Match_3\main(null);
}
