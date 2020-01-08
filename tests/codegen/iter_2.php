<?php

namespace iter_2 {
  function loop_body($i, $s) {
    print((string)$i . " " . $s . "\n");
  }

  // #[entry]
  function main() {
    foreach ([
      "Rick",
      "Morty",
      "Summer"
    ] as $d => $c) {
      '\iter_2\loop_body'($d, $c);
    }
  }
}

namespace {
  \iter_2\main();
}
