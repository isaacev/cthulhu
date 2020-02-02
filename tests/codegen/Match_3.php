<?php

namespace Match_3 {
  function main() {
    $b = 5;
    if ($b == 0) {
      $c = 0;
    } else if (true) {
      $c = $b + 1;
    } else {
      die("match expression did not cover all possibilities\n");
    }
    print((string)$c . "\n");
  }
}

namespace {
  \Match_3\main(null);
}
