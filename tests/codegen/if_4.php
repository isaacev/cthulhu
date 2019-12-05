<?php

namespace if_4 {
  // #[entry]
  function main() {
    if (true) {
      $a = "hello";
    } else {
      $a = "world";
    }
    $x = $a;
  }
}

namespace {
  \if_4\main();
}
