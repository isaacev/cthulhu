<?php

namespace match_1 {
  // #[entry]
  function main() {
    $b = 2 + 2;
    if ($b == 0) {
      print("zero\n");
    } else if ($b == 1) {
      print("one\n");
    } else if ($b == 2) {
      print("two\n");
    } else if (true) {
      print("several\n");
    }
    $a;
  }
}

namespace {
  \match_1\main();
}
