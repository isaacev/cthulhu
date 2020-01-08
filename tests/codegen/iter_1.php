<?php

namespace iter_1 {
  // #[entry]
  function main() {
    foreach ([
      "Rick",
      "Morty",
      "Summer"
    ] as $c) {
      print($c . "\n");
    }
  }
}

namespace {
  \iter_1\main();
}
