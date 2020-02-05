<?php

namespace Match_1 {
  function main() {
    $b = 2 + 2;
    if ($b == 0) {
      print("zero" . "\n");
    } else if ($b == 1) {
      print("one" . "\n");
    } else if ($b == 2) {
      print("two" . "\n");
    } else if (true) {
      print("several" . "\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
  }
}

namespace {
  \Match_1\main(null);
}
