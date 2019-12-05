<?php

namespace match_2 {
  // #[entry]
  function main() {
    $b = "abc";
    if ($b == "") {
      $a = 0;
    } else if ($b == "abc") {
      $a = 3;
    } else if (true) {
      $_a = $b;
      $a = -1;
    }
    $x = $a;
    $y = $x + 1;
  }
}

namespace {
  \match_2\main();
}
