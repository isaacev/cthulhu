<?php

namespace Match_1 {
  function main() {
    $a = 2 + 2;
    if ($a == 0) {
      print("zero" . "\n");
    } else if ($a == 1) {
      print("one" . "\n");
    } else if ($a == 2) {
      print("two" . "\n");
    } else if (true) {
      print("several" . "\n");
    } else {
      die("match expression did not cover all possibilities\n");
    }
    return null;
  }
}

namespace {
  \Match_1\main(null);
}
