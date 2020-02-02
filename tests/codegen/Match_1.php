<?php

namespace Match_1 {
  function main() {
    $b = 2 + 2;
    if ($b == 0) {
      $c = print("zero" . "\n");
    } else if ($b == 1) {
      $c = print("one" . "\n");
    } else if ($b == 2) {
      $c = print("two" . "\n");
    } else if (true) {
      $c = print("several" . "\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
    $c;
  }
}

namespace {
  \Match_1\main(null);
}
