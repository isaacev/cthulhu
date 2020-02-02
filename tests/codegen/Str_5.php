<?php

namespace str_5 {
  // #[entry]
  function main() {
    $hello = "world";
    print("\$\$hello\n");
  }
}

namespace {
  \str_5\main();
}
